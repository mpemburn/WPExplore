<?php

use App\Generators\BlogsCsvGenerator;
use App\Generators\LegacyAppsCsvGenerator;
use App\Generators\PluginsCsvGenerator;
use App\Http\Controllers\BlogCrawlerController;
use App\Models\Blog;
use App\Models\BlogList;
use App\Models\CfLegacyApp;
use App\Models\DevBrokenPage;
use App\Models\LogParserCompleted;
use App\Models\Option;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\TestingBrokenPage;
use App\Models\WordpressProductionLink;
use App\Models\WordPressTestBrokenPage;
use App\Models\WordpressTestLink;
use App\Observers\BugObserver;
use App\Services\BlogCrawlerService;
use App\Services\BlogService;
use App\Services\BugScanService;
use App\Services\CloneService;
use App\Services\ColdFusionLegacyAppService;
use App\Services\DatabaseService;
use App\Services\FileService;
use App\Services\LogParserService;
use App\Services\UrlService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RedirectMiddleware;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use App\Observers\BlogObserver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Spatie\Crawler\Crawler;
use Spatie\Async\Pool;
use Symfony\Component\Process\Process;
use function Sentry\captureException;
use Smalot\PdfParser\Parser;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/dev', function () {
    $databases = [];
    collect(explode(',', env('INSTALLED_DATABASES')))
        ->each(function ($db) use (&$databases) {
            $parts = explode(':', $db);
            $databases[$parts[0]] = $parts[1];
        });

    dd($databases);
    // Do what thou wilt
});

Route::get('/', fn() => view('welcome'));

Route::get('/search', 'App\Http\Controllers\SearchController@search');
Route::post('/do_search', 'App\Http\Controllers\SearchController@index');

Route::get('/csv/active', fn() => (new BlogService())->createActiveBlogsCsv());

Route::get('/csv/stale', fn() => (new BlogService())->createStaleBlogsCsv());

Route::get('/csv/mat', fn() => (new BlogService())->createMatBlogsCsv());

Route::get('/csv/active/date', fn() => (new BlogService())->createBlogsInDateRangeCsv(request('start'), request('end')));

Route::get('/csv/mat/date', fn() => (new BlogService())->createMatBlogsInDateRangeCsv(request('start'), request('end')));

Route::get('/load_blogs', function () {
    $currentSite = request('site');
    DatabaseService::setDb($currentSite . '_clarku');

    $blogs = (new BlogService())->getActiveBlogs();

    DatabaseService::setDb('server_tests');
    DB::purge('mysql');
    $blogs->each(function ($blog) use ($currentSite) {
        BlogList::create([
            'site' => $currentSite,
            'blog_id' => $blog['blog_id'],
            'blog_url' => $blog['siteurl'],
            'last_updated' => $blog['last_updated'],
            'admin_email' => $blog['admin_email'],
            'current_theme' => isset($blog['current_theme']) ? $blog['current_theme'] : '',
            'template' => $blog['template'],
        ]);
    });


});

Route::get('/csv/cfapps', function () {
    $titles = [
        'URL',
        'Title',
        'Redirect',
        'Error'
    ];
    echo implode(',', $titles) . '<br>';

    CfLegacyApp::where('server', 'Wilbur')->each(function ($app) {
        $row = [
            $app->index_url,
            $app->page_title,
            $app->redirect_url,
            $app->error_code,
        ];

        echo implode(',', $row) . '<br>';
    });
});

Route::get('/gen_redirects', function () {
    // Get CSV from John's spreadsheet
    $redirects = Storage::path('redirects.csv');
    if (file_exists($redirects)) {
        if (($open = fopen($redirects, "r")) !== FALSE) {
            // Iterate over lines
            while (($data = fgetcsv($open, 1000, ",")) !== FALSE) {
                // The $data array contains two fields: $id and $urk
                $id = $data[0];
                $url = $data[1];

                // Build the rule from the pattern used in the original
                $rule = '
                <rule name="faculty/facultybio.cfm?id=' . $id . '" stopProcessing="true">
                    <match url="faculty/facultybio.cfm"/>
                    <conditions>
                        <add input="{QUERY_STRING}" pattern="^([^&amp;]*&amp;)?id=' . $id . '(&amp;[^&amp;]*)?$"/>
                    </conditions>
                    <action type="Redirect" url="https://' . trim($url) . '"/>
                </rule>' . PHP_EOL;

                // echo the rule so that it can be copied from the page source.
                echo $rule;
            }
            fclose($open);
        }
    }
});

Route::get('/repaired', function () {
    $subset = collect();
    $testLinks = (new WordpressTestLink())
        ->where('found', 0)
        ->where('link_url', 'NOT LIKE', '%blogs.dir%');
    $testLinks->each(function ($link) use (&$subset) {
        $linkUrl = $link->link_url;
        $parts = pathinfo($linkUrl);
        $path = str_replace(['https:', 'wp-content/uploads/sites/'], '', $parts['dirname']) . '/';
        if ($subset->contains($path)) {
            return;
        }
        $subset->push($path);
        $subset->put($path, $link->blog_id);
    });
    $commands = collect();
    $command = null;
    $subset->each(function ($path) use (&$commands, &$command) {
        if (!$command) {
            $escaped = str_replace('/', '\/', $path);
            $command = 'wp search-replace "' . $escaped . '" "' . $path . '"';
        } else {
            $command .= ' wp_' . $path . '_posts --network';
            $commands->push($command);
            $command = null;
        }
    });
    $commands->each(function ($command) {
        echo $command . '<br>';
    });

});

Route::get('/broken', function () {
    $testLinks = (new WordpressTestLink())->where('found', 0);
    echo '<table>';
    echo '<tr>';
    echo '<th>';
    echo 'Blog Page';
    echo '</th>';
    echo '<th>';
    echo 'Link';
    echo '</th>';
    echo '<th>';
    echo 'Last Updated';
    echo '</th>';
    echo '</tr>';
    $testLinks->each(function ($testLink) {
        $isBlogsDir = false;
        $blogId = $testLink->blog_id;
        $blogUrl = $testLink->page_url;
        $oldLink = $testLink->link_url;
        $blog = BlogList::where('site', 'wordpress')->where('blog_id', $blogId);
        $lastUpdated = $blog->first()->last_updated;
        $siteName = str_replace('https://wordpress.clarku.edu/', '', $blog->first()->blog_url);
        if (stripos($oldLink, 'blogs.dir')) {
            $isBlogsDir = true;
            $replaceLink = str_replace("{$siteName}/wp-content/blogs.dir/{$blogId}/files", "wp-content/uploads/sites/{$blogId}", $oldLink);
        } else {
            $replaceLink = str_replace("{$siteName}/files", "wp-content/uploads/sites/{$blogId}", $oldLink);
        }
        if ($isBlogsDir) {
            $code = (new UrlService())->testUrl($replaceLink);
            if ($code === 404) {
                $prodLink = str_replace('/test', '', $oldLink);
                $prodCode = (new UrlService())->testUrl($prodLink);
                if ($prodCode !== 200) {
                    echo '<tr>';
                    echo '<td>';
                    echo $blogUrl;
                    echo '</td>';
                    echo '<td>';
                    echo $prodLink;
                    echo '</td>';
                    echo '<td>';
                    echo $lastUpdated;
                    echo '</td>';
                    echo '</tr>';
//                    !d($oldLink, $replaceLink, $prodCode);
                }
            }
        }
    });
    echo '<table>';
});

Route::get('/parse_log', function () {
    $parser = new LogParserService();
    $parser->setCodePath('/Users/mpemburn/Dev/clarku-wordpress')
        ->setAppPath('dom28151\\')
        ->run('28151_04_24', ['wp-content/themes/clarku'], []);

    return view('logparser', [
        'logPrefix' => $parser->getLogPrefix(),
        'basePath' => $parser->getCodePath(),
        'appPath' => $parser->getAppPath(),
        'data' => $parser->display()
    ]);
});

Route::get('/to_archive', function () {
    $mats = (new BlogService())->getActiveBlogs(['/mat'], '2013/01/10', '2018/12/31');

    $exclude = ['210', '212', '405', '400', '416', '413', '396', '409', '398', '450', '664'];

    $additions = (new BlogService())->getBlogsById(['539', '101', '536', '402', '548']);

    $mats = $mats->merge($additions);

    $count = 0;
    $mats->each(function ($blog) use (&$count, $exclude) {
        if (in_array($blog['blog_id'], $exclude)) {
            return;
        }
        echo "blogs[{$count}]=\"{$blog['blog_id']}\" # {$blog['siteurl']}" . '<br>';

        $count++;
    });
});

Route::get('/where_active', function () {
    $currentSite = request('site');
    $notFoundOnly = request('not_found');

    DatabaseService::setDb($currentSite . '_clarku');

    $rootPath = Storage::path('plugins_json');
    $file = $rootPath . "/{$currentSite}_plugins.json";

    if (file_exists($file)) {
        $json = file_get_contents($file);
        collect(json_decode($json, true))->each(function ($row) use ($notFoundOnly) {
            if ($row['status'] !== 'inactive') {
                return;
            }
            $pluginName = $row['name'];
            $title = $row['title'];
            echo (new BlogService())->findPluginInSubsite($pluginName, $title, $notFoundOnly);
        });
    }
});

Route::get('/themes', function () {
    $currentSite = request('site');
    $notFoundOnly = request('not_found');

    DatabaseService::setDb($currentSite . '_clarku');

    $rootPath = Storage::path('themes_json');
    $file = $rootPath . "/{$currentSite}_themes.json";
    if (file_exists($file)) {
        $json = file_get_contents($file);
        collect(json_decode($json, true))->each(function ($row) use ($notFoundOnly) {
            if ($row['status'] !== 'inactive') {
                return;
            }
            $themeName = $row['title'];
            echo (new BlogService())->findThemesInSubsite($themeName, $notFoundOnly);
        });
    }
});

Route::get('/active', function () {
    $currentSite = request('site');
    if ($currentSite) {
        DatabaseService::setDb($currentSite . '_clarku');
    }

    $blogs = (new BlogService())->getActiveBlogs();

    !d($blogs->toArray());
});

Route::get('/sentry', function () {
    \Sentry\init(['dsn' => env('WP_SENTRY_PHP_DSN')]);
    try {
        $this->functionBogus();
    } catch (\Throwable $exception) {
        $result = captureException($exception);

        !d($result);
    }

});

Route::get('/phpdd', function () {
    $pluginPath = "C:/Users/mpemburn/Documents/Dev/www.clarku.edu/wp-content/plugins/";
    $json = file_get_contents('C:/Users/mpemburn/Documents/Sandbox/wpexplore/storage/app/public/PluginJSON/wordfence.json');
    $data = collect(json_decode($json, true));

    !d($data);
});

Route::get('/distill', function () {
    $rootPath = Storage::path('plugins_json');
    $distilled = collect();
    collect(File::allFiles($rootPath))->each(function ($file) use (&$distilled) {
        $info = pathinfo($file);
        $site = str_replace('_plugins', '', $info['filename']);

        $json = file_get_contents($file);
        collect(json_decode($json, true))->each(function ($row) use ($site, &$distilled) {
            if ($row['status'] !== 'active' && $row['status'] !== 'active-network') {
                return;
            }
            $plugin = $row['name'];
            if (!$distilled->contains($plugin)) {
                $distilled->push($plugin);
                $sites = collect();
                $sites->push($site);
                $distilled->put($plugin, ['data' => $row, 'sites' => $sites]);
            } else {
                $data = $distilled->get($plugin);
                $data['sites']->push($site);
                $distilled->put($plugin, $data);
            }
        });
    });
    $rows = collect();
    $distilled->each(function ($plugin) use (&$rows) {
        if (is_array($plugin)) {
            $data = $plugin['data'];
            unset($data['version'], $data['status'], $data['update']);
            $data['sites'] = implode(',', $plugin['sites']->sortDesc()->toArray());
            $rows->push($data);
        }
    });
    return (new PluginsCsvGenerator('distilled_plugin_list.csv'))
        ->setData($rows)
        ->run();

});


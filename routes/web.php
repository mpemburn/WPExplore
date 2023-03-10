<?php

use App\Generators\BlogsCsvGenerator;
use App\Generators\PluginsCsvGenerator;
use App\Http\Controllers\BlogCrawlerController;
use App\Models\Blog;
use App\Models\BlogList;
use App\Models\DevBrokenPage;
use App\Models\Option;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\TestingBrokenPage;
use App\Models\WordpressProductionLink;
use App\Models\WordPressTestBrokenPage;
use App\Models\WordpressTestLink;
use App\Observers\BugObserver;
use App\Services\BlogService;
use App\Services\BugScanService;
use App\Services\CloneService;
use App\Services\DatabaseService;
use App\Services\LogParserService;
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

Route::get('/', fn() => view('welcome'));

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

Route::get('/func', function () {
    class Update
    {
        protected $versions = [
            '20140709',
            '20160831',
            '20170510',
            '20170511',
            '20170711',
            '20171023',
            '20171215',
            '20171219',
            '20190227',
            '20200331',
            '20201217',
            '20210624',
            '20210924',
            '20220222',
            '20220426',
            '20220506',
            '20220818',
            '20221101',
        ];

        public function __construct()
        {
            $needs_updating = false;
            $auth_version = '20220426';

            foreach ($this->versions as $version) {
                if ( false === $auth_version || intval( $auth_version ) < $version ){
                    if (method_exists($this, 'update_' . $version)) {
                        $auth_version = call_user_func_array([$this, 'update_' . $version], [$auth_version, $version]);
                        $needs_updating = true;
                    }
                }
            }
        }

        protected function update_20221101( $auth_version, $version )
        {
            echo 'Hi! ' . $auth_version . '<br>';
            echo 'Yo! ' . $version . '<br>';

            return $version;
        }
    }

    new Update();
});

Route::get('/dev', function () {
    $myArray = [];

    $result = $myArray['element'] ?? null;

    !d($result);
});

Route::get('/parse_log', function () {
    new LogParserService('28151_3_10', ['wp-content/themes/clarku'], []);
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
    $file =  $rootPath . "/{$currentSite}_plugins.json";

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
    $file =  $rootPath . "/{$currentSite}_themes.json";
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
        collect(json_decode($json, true))->each(function ($row) use ($site, &$distilled){
            if ($row['status'] !== 'active' && $row['status'] !== 'active-network') {
                return;
            }
            $plugin = $row['name'];
            if (! $distilled->contains($plugin)) {
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


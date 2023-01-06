<?php

use App\Http\Controllers\BlogCrawlerController;
use App\Models\Blog;
use App\Models\Option;
use App\Models\Post;
use App\Models\PostMeta;
use App\Models\WordpressProductionLink;
use App\Models\WordpressTestLink;
use App\Observers\BugObserver;
use App\Services\BlogService;
use App\Services\BugScanService;
use App\Services\CloneService;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use App\Observers\BlogObserver;
use Illuminate\Support\Facades\Storage;
use Spatie\Crawler\Crawler;
use Spatie\Async\Pool;
use Symfony\Component\Process\Process;
use function Sentry\captureException;

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

Route::get('/dev', function () {
    $csv = Storage::path('public/plugins.csv');
    if (file_exists($csv)) {
        $row = 1;
        if (($handle = fopen($csv, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $num = count($data);
                echo "<p> $num fields in line $row: <br /></p>\n";
                $row++;
                for ($c=0; $c < $num; $c++) {
                    echo $data[$c] . "<br />\n";
                }
            }
            fclose($handle);
        }    }
    // Do what thou wilt
});

Route::get('/themes', function () {
    $themes = collect();
    (new BlogService())->getActiveBlogs()->each(function ($blog) use ($themes) {
        $theme = isset($blog['template']) ? $blog['template'] : null;
        if ($theme && ! $themes->has($theme)) {
            $themes->push($theme);
        }
        if (isset($blog['siteurl'])) {
            $themes->put($theme, $blog['siteurl']);
        }
    });
    !d($themes);

//    collect([
//        '2010-weaver',
//        'attitude',
//        'blogolife',
//        'copyblogger',
//        'custom-community',
//        'customizr',
//        'evolve',
//        'indore',
//        'ivanhoe',
//        'news-magazine-theme-640',
//        'origin',
//        'oxygen',
//        'parabola',
//        'responsive',
//        'sorbet',
//        'sydney',
//        'twentyeleven',
//        'twentyfifteen',
//        'twentyfourteen',
//        'twentyseventeen',
//        'twentysixteen',
//        'twentyten',
//        'twentythirteen',
//        'twentytwelve',
//        'twentytwenty',
//        'twentytwentytwo',
//        'veryplaintxt-20',
//        'weaver',
//        'weaver-ii-pro',
//        'zeesynergie',
//        'zerif-lite',
//    ])->each(function ($theme) use ($blogs) {
//        $using = $blogs->where('template', $theme);
//        !d($using);
//    });
});

Route::get('/active', function () {
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

Route::get('/cron', function () {
    $path = Storage::path('public');
    $file = $path . '/cronlist.json';
    $json = file_get_contents($file);

    echo '<table>';
    collect(json_decode($json, true))->each(function ($row) {
        echo '<tr>';

        $row['time'] = Carbon::createFromTimestamp($row['time'])
            ->setTimezone('America/New_York')
            ->format('m-d-Y g:i:s A');
        echo "<td>{$row['hook']}</td>";
        echo "<td>{$row['time']}</td>";
        echo "<td>{$row['recurrence']}</td>";
        echo '</tr>';
    });
    echo '</table>';


});


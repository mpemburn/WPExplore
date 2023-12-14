<?php

use App\Exports\Sheets\WebArchiveSheet;
use App\Exports\WebArchiveExport;
use App\Generators\BlogsCsvGenerator;
use App\Models\Blog;
use App\Models\BlogList;
use App\Models\CfLegacyApp;
use App\Models\CfLegacyAppBaseline;
use App\Models\Option;
use App\Models\Post;
use App\Models\SitesProductionBrokenPage;
use App\Models\WebArchiveTest;
use App\Observers\BlogObserver;
use App\Observers\WebCrawlObserver;
use App\Observers\WebObserver;
use App\Services\BlogService;
use App\Services\CsvService;
use App\Services\DatabaseImportService;
use App\Facades\Database;
use App\Services\FileService;
use App\Services\LogParserService;
use App\Facades\Curl;
use App\Services\WebArchiveService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Spatie\Crawler\Crawler;
use Symfony\Component\Process\Process;
use Spatie\Async\Pool;
use Ahc\Jwt\JWT;

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

Route::get('/sites', function () {
    $baseUrl = 'https://web.clarku.edu/';

    (new WebArchiveService())
        ->setServer('wilbur_login')
        ->setBaseUrl($baseUrl)
        ->setFilePath('cf_apps/logincfm_wilbur.txt')
        ->gather();

//    (new WebArchiveService())
//        ->setBaseUrl($baseUrl)
//        ->setServer('charlotte')
//        ->setFilePath('')
//        ->setDataModel(new WebArchiveTest())
//        ->runTests();
 });

Route::get('/dev', function () {
    return (new WebArchiveExport('charlotte'))->download('charlotte_cfm.xlsx');
//    $query = WebArchiveTest
//        ::query()
//        ->select(['web_root', 'page_title'])
//        ->where('redirect_url', '0')
//        ->where('web_root', 'NOT LIKE', '%delete_%')
//        ->where('server', 'charlotte')
//        ->where('category', 'departments')
//        ->get();
//
////    !d($query);
//    $query->each(function ($row) {
//        !d($row->web_root);
//    });

    // Do what thou wilt
});

Route::get('/portal', function () {
    return view('portal');
});

Route::get('/', fn() => view('dashboard'))->name('dashboard');

Route::get('/search', 'App\Http\Controllers\SearchController@index')->name('index');
Route::post('/do_search', 'App\Http\Controllers\SearchController@search')->name('search');

Route::get('/csv', 'App\Http\Controllers\CsvController@index')->name('csv');
Route::get('/min_date', 'App\Http\Controllers\CsvController@getMinDate')->name('csv_min_date');
Route::post('/do_download', 'App\Http\Controllers\CsvController@download')->name('csv_download');

Route::get('/migrate', 'App\Http\Controllers\MigrationController@index')->name('migrate');
Route::get('/subsites', 'App\Http\Controllers\MigrationController@getSubsites')->name('subsites');
Route::post('/do_migration', 'App\Http\Controllers\MigrationController@migration')->name('migration');

Route::get('/load_blogs', function () {
    $currentSite = request('site');
    Database::setDb($currentSite . '_clarku');

    $blogs = (new BlogService())->getActiveBlogs();

    Database::setDb('server_tests');
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

Route::get('/where_active', function () {
    $currentSite = request('site');
    $notFoundOnly = request('not_found');

    Database::setDb($currentSite . '_clarku');

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

Route::get('/active', function () {
    $currentSite = request('site');
    if ($currentSite) {
        Database::setDb($currentSite . '_clarku');
    }

    $blogs = (new BlogService())->getActiveBlogs();

    $blogs->each(function ($blog) {
        echo $blog['blog_id'] . '<br>';
    });
});


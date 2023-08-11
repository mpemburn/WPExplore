<?php

use App\Models\Blog;
use App\Models\BlogList;
use App\Models\CfLegacyApp;
use App\Observers\BlogObserver;
use App\Observers\WebCrawlObserver;
use App\Observers\WebObserver;
use App\Services\BlogService;
use App\Services\DatabaseService;
use App\Services\LogParserService;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Spatie\Crawler\Crawler;


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
    // Do what thou wilt
});

Route::get('/', fn() => view('dashboard'))->name('dashboard');

Route::get('/search', 'App\Http\Controllers\SearchController@search')->name('search');
Route::post('/do_search', 'App\Http\Controllers\SearchController@index');

Route::get('/csv', 'App\Http\Controllers\CsvController@index')->name('csv');
Route::get('/min_date', 'App\Http\Controllers\CsvController@getMinDate')->name('min_date');

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

Route::get('/active', function () {
    $currentSite = request('site');
    if ($currentSite) {
        DatabaseService::setDb($currentSite . '_clarku');
    }

    $blogs = (new BlogService())->getActiveBlogs();

    !d($blogs->toArray());
});


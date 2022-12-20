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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use App\Observers\BlogObserver;
use Illuminate\Support\Facades\Storage;
use Spatie\Crawler\Crawler;
use Spatie\Async\Pool;

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
    // Do what thou wilt
});

Route::get('/themes', function () {
    collect([
        '2010-weaver',
        'attitude',
        'blogolife',
        'copyblogger',
        'custom-community',
        'customizr',
        'evolve',
        'indore',
        'ivanhoe',
        'news-magazine-theme-640',
        'origin',
        'oxygen',
        'parabola',
        'responsive',
        'sorbet',
        'sydney',
        'twentyeleven',
        'twentyfifteen',
        'twentyfourteen',
        'twentyseventeen',
        'twentysixteen',
        'twentyten',
        'twentythirteen',
        'twentytwelve',
        'twentytwenty',
        'twentytwentytwo',
        'veryplaintxt-20',
        'weaver',
        'weaver-ii-pro',
        'zeesynergie',
        'zerif-lite',
    ])->each(function ($theme) {
        $blogs = Blog::where();
    });
});

Route::get('/active', function () {
    $blogs = (new BlogService())->getActiveBlogs();

    !d($blogs->toArray());
});


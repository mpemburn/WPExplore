<?php

use App\Http\Controllers\BlogCrawlerController;
use App\Models\Blog;
use App\Models\Option;
use App\Models\Post;
use App\Models\WordpressProductionLink;
use App\Services\BlogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;
use App\Observers\BlogObserver;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/csv/active', function () {
    return (new BlogService())->createActiveBlogsCsv();
});

Route::get('/csv/stale', function () {
    return (new BlogService())->createStaleBlogsCsv();
});

Route::get('/csv/mat', function () {
    return (new BlogService())->createMatBlogsCsv();
});

Route::get('/dev', function () {
    // Do what thou wilt.
});

Route::get('/active', function () {
    $blogs = (new BlogService())->getActiveBlogs();

    !d($blogs->toArray());
});

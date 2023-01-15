<?php

namespace App\Models;

/**
 * @method  where(string $string, $blog_id)
 */
class NewsTestBrokenPage extends Link
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'news_test_broken_pages';
    protected string $site = 'news';
    protected string $blogBasePath = 'news.test.clarku.edu';
}
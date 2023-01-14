<?php

namespace App\Models;

/**
 * @method  where(string $string, $blog_id)
 */
class WordPressTestBrokenPage extends Link
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'wordpress_test_broken_pages';
    protected string $site = 'wordpress';
    protected string $blogBasePath = 'wordpress.test.clarku.edu';
}

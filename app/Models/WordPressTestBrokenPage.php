<?php

namespace App\Models;

class WordPressTestBrokenPage extends Link
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'wordpress_test_broken_pages';
    protected string $blogBasePath = 'wordpress.test.clarku.edu';
}

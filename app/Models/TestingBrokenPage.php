<?php

namespace App\Models;

class TestingBrokenPage extends Link
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'dev_broken_pages';
    protected string $blogBasePath = 'www.testing.clarku.edu';
}

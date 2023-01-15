<?php

namespace App\Models;

/**
 * @method  where(string $string, $blog_id)
 */
class FutureTestBrokenPage extends Link
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'future_test_broken_pages';
    protected string $site = 'future';
    protected string $blogBasePath = 'future.test.clarku.edu';
}

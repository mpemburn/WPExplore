<?php

namespace App\Models;

/**
 * @method  where(string $string, $blog_id)
 */
class FutureProductionBrokenPage extends Link
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'future_broken_pages';
    protected string $site = 'future';
    protected string $blogBasePath = 'future.clarku.edu';
}

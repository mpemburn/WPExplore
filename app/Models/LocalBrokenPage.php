<?php

namespace App\Models;

/**
 * @method  where(string $string, $blog_id)
 */
class LocalBrokenPage extends Link
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'local_broken_pages';
    protected string $site = 'www';
    protected string $blogBasePath = 'clarku.test';
}

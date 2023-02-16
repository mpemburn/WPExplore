<?php

namespace App\Models;

/**
 * @method  where(string $string, $blog_id)
 */
class ClarkNowBrokenPage extends Link
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'clark_now_broken_pages';
    protected string $site = 'news';
    protected string $blogBasePath = 'clarknow.clarku.edu';
}

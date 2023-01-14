<?php

namespace App\Models;

/**
 * @method  where(string $string, $blog_id)
 */
class WordPressProductionBrokenPage extends Link
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'wordpress_production_broken_pages';
    protected string $site = 'wordpress';
    protected string $blogBasePath = 'wordpress.clarku.edu';
}

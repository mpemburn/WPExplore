<?php

namespace App\Models;

class WordPressProductionBrokenPage extends Link
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'wordpress_production_broken_pages';
    protected string $blogBasePath = 'wordpress.clarku.edu';
}

<?php

namespace App\Models;

class ProductionBrokenPage extends Link
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'production_broken_pages';
    protected string $blogBasePath = 'www.clarku.edu';
}

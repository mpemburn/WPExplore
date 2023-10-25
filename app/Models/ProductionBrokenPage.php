<?php

namespace App\Models;

/**
 * @method  where(string $string, $blog_id)
 */
class ProductionBrokenPage extends Link
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'www_broken_pages';
    public ?string $sourceDb = 'www_clarku';
    protected string $site = 'www';
    protected string $blogBasePath = 'www.clarku.edu';
}

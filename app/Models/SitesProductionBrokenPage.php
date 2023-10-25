<?php

namespace App\Models;

/**
 * @method  where(string $string, $blog_id)
 */
class SitesProductionBrokenPage extends Link
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'sites_broken_pages';
    public ?string $sourceDb = 'sites_clarku';
    protected string $site = 'sites';
    protected string $blogBasePath = 'sites.clarku.edu';
}

<?php

namespace App\Models;

/**
 * @method  where(string $string, $blog_id)
 */
class SitesTestBrokenPage extends Link
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'sites_test_broken_pages';
    public ?string $sourceDb = 'sites_clarku';
    protected string $site = 'sites';
    protected string $blogBasePath = 'sites.test.clarku.edu';
}

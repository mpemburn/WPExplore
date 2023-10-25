<?php

namespace App\Models;

/**
 * @method  where(string $string, $blog_id)
 */
class SandboxProductionBrokenPage extends Link
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'sandbox_broken_pages';
    protected string $site = 'sandbox';
    protected string $blogBasePath = 'sandbox.clarku.edu';
}

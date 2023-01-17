<?php

namespace App\Models;

class DevBrokenPage extends Link
{
    protected const AUTH_USERNAME = 'WWWDEV_USERNAME';
    protected const AUTH_PASSWORD = 'WWWDEV_PASSWORD';

    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'www_dev_broken_pages';
    protected string $site = 'www';
    protected string $blogBasePath = 'www.dev.clarku.edu';
}

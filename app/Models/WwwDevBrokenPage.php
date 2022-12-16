<?php

namespace App\Models;

class WwwDevBrokenPage extends Link
{
    protected const AUTH_USERNAME = 'WWWDEV_USERNAME';
    protected const AUTH_PASSWORD = 'WWWDEV_PASSWORD';

    public $table = 'broken_pages';
    protected string $blogBasePath = 'www.dev.clarku.edu';
}

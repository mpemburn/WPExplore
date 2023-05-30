<?php

namespace App\Models;

class WordpressTestLink extends Link
{
    public $table = 'test_links';
    protected string $site = 'wordpress';

    protected string $blogBasePath = 'wordpress.test.clarku.edu';
    protected array $alternateImagePaths = ['s28811.pcdn.co'];
}

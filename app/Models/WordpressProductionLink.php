<?php

namespace App\Models;

class WordpressProductionLink extends Link
{
    public $table = 'production_links';

    protected string $blogBasePath = 'wordpress.clarku.edu';
    protected array $alternateImagePaths = ['s29121.pcdn.co'];
}

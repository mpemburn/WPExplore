<?php

namespace App\Models;

class ClarkProductionLink extends Link
{
    public $table = 'clark_links';

    protected string $blogBasePath = 'www.clarku.edu';
    protected array $alternateImagePaths = ['s28151.pcdn.co'];
}

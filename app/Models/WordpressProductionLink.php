<?php

namespace App\Models;

/**
 * @method  where(string $string, $blog_id)
 */
class WordpressProductionLink extends Link
{
    public $table = 'production_links';

    protected string $blogBasePath = 'wordpress.clarku.edu';
    protected array $alternateImagePaths = ['s29121.pcdn.co'];

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement @method  where(string $string, $blog_id)
    }
}

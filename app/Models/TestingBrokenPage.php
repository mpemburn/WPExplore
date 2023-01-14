<?php

namespace App\Models;

/**
 * @method  where(string $string, $blog_id)
 */
class TestingBrokenPage extends Link
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'www_testing_broken_pages';
    protected string $blogBasePath = 'www.testing.clarku.edu';

    public function __call(string $name, array $arguments)
    {
        // TODO: Implement @method  where(string $string, $blog_id)
    }
}

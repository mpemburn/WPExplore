<?php

namespace App\Models;

/**
 * @method  where(string $string, $blog_id)
 */
class TrainingBrokenPage extends Link
{
    public const AUTH_USERNAME = 'WWWDEV_USERNAME';
    public const AUTH_PASSWORD = 'WWWDEV_PASSWORD';

    protected $fillable = [
        'blog_id',
        'page_url',
        'error'
    ];

    public $table = 'www_training_broken_pages';
    public ?string $sourceDb = 'www_clarku';
    protected string $site = 'www';
    protected string $blogBasePath = 'www.training.clarku.edu';
}

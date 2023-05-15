<?php

namespace App\Models;

use App\Interfaces\FindableLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use PDOException;

abstract class Link extends Model implements FindableLink
{
    public const AUTH_USERNAME = null;
    public const AUTH_PASSWORD = null;

    protected string $site = '';
    protected string $blogBasePath = '';
    protected array $alternateImagePaths = [];

    protected $fillable = [
        'blog_id',
        'page_url',
        'link_url',
        'found'
    ];

    public function __construct()
    {
        if (! Schema::hasTable($this->getTable())) {
            throw new PDOException('Table "' . $this->getTable() . '" not found in ' . static::class);

        }

        parent::__construct();
    }

    public function getSite(): string
    {
        return $this->site;
    }

    public function getAuth(array $options): array
    {
        if (static::AUTH_USERNAME && static::AUTH_PASSWORD) {
            $username = env(static::AUTH_USERNAME);
            $password = env(static::AUTH_PASSWORD);

            $options = array_merge($options, ['auth' => [$username, $password]]);
        }

        return $options;
    }

    public function getBlogBasePath(): string
    {
        return $this->blogBasePath;
    }

    public function foundInAlternateImagePath(string $path): bool
    {
        return (str_replace($this->alternateImagePaths, '', $path) != $path);
    }

    public function replaceBasePath(string $url): string
    {
        $parts = parse_url($url);
        $path = $parts['path'] ?? null;

        return $parts['scheme'] . '://' . $this->blogBasePath . $path;
    }

    public function matchesBasePath(string $url): bool
    {
        return preg_match('/(https|http)(:\/\/)' . $this->blogBasePath . '(.*)/', $url);
    }

    public function urlExists(string $url): bool
    {
        return $this->where('page_url', $url)->exists();
    }

}

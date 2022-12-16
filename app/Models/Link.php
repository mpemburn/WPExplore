<?php

namespace App\Models;

use App\Interfaces\FindableLink;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use PDOException;

abstract class Link extends Model implements FindableLink
{
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

}

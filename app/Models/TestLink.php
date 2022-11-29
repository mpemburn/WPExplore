<?php

namespace App\Models;

use App\Interfaces\FindableLink;
use Illuminate\Database\Eloquent\Model;

class TestLink extends Model implements FindableLink
{
    protected string $blogBasePath = 'wordpress.test.clarku.edu';
    protected string $linkBasePath = '/wp-content/uploads/sites';

    protected $fillable = [
        'blog_id',
        'page_url',
        'link_url',
        'found'
    ];

    public function getBlogBasePath(): string
    {
        return $this->blogBasePath;
    }

    public function getLinkBasePath(): string
    {
        return $this->linkBasePath;
    }

    public function replaceBasePath(string $url): string
    {
        $parts = parse_url($url);

        return $parts['scheme'] . '://' . $this->blogBasePath . $parts['path'];
    }

}

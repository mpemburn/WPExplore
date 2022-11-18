<?php

namespace App\Models;

use App\Interfaces\FindableLink;
use Illuminate\Database\Eloquent\Model;

class ProductionLink extends Model implements FindableLink
{
    protected string $blogBasePath = 'wordpress.clarku.edu';
    protected string $linkBasePath = '/files';

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

}

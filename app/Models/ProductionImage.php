<?php

namespace App\Models;

use App\Interfaces\FindableImage;
use Illuminate\Database\Eloquent\Model;

class ProductionImage extends Model implements FindableImage
{
    protected string $blogBasePath = 'wordpress.clarku.edu';
    protected string $imageBasePath = '/files';

    protected $fillable = [
        'blog_id',
        'page_url',
        'image_url',
        'found'
    ];

    public function getBlogBasePath(): string
    {
        return $this->blogBasePath;
    }

    public function getImageBasePath(): string
    {
        return $this->imageBasePath;
    }

}

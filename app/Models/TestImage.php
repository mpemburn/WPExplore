<?php

namespace App\Models;

use App\Interfaces\FindableImage;
use Illuminate\Database\Eloquent\Model;

class TestImage extends Model implements FindableImage
{
    protected string $blogBasePath = 'wordpress.test.clarku.edu';
    protected string $imageBasePath = '/wp-content/uploads/sites';

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

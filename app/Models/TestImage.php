<?php

namespace App\Models;

use App\Interfaces\FindableImage;
use Illuminate\Database\Eloquent\Model;

class TestImage extends Model implements FindableImage
{
    protected string $basePath = '/wp-content/uploads/sites';

    protected $fillable = [
        'blog_id',
        'page_url',
        'image_url',
        'found'
    ];

    public function getBasePath(): string
    {
        return $this->basePath;
    }

}

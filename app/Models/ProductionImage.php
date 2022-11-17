<?php

namespace App\Models;

use App\Interfaces\FindableImage;
use Illuminate\Database\Eloquent\Model;

class ProductionImage extends Model implements FindableImage
{
    protected string $basePath = '/files';

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

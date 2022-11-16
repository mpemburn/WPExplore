<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FoundImage extends Model
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'image_url',
        'original_exists',
        'success'
    ];
}

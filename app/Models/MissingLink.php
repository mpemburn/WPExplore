<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MissingLink extends Model
{
    protected $fillable = [
        'blog_id',
        'page_url',
        'link_url',
        'found'
    ];

}

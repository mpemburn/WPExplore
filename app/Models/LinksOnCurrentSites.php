<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LinksOnCurrentSites extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'link',
        'post_date',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CfLinks extends Model
{
    use HasFactory;

    protected $fillable = [
        'server',
        'category',
        'url',
        'redirect',
    ];
}

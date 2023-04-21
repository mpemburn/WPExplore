<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoundText extends Model
{
    use HasFactory;

    public $table = 'found_text';
    public $fillable = [
        'search_string',
        'found_url',
        'found_in',
    ];
}

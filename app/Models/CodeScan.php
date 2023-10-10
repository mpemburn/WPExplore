<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeScan extends Model
{
    use HasFactory;

    public $fillable = [
        'key_word',
        'filename',
        'line_num',
        'line_contents',
    ];

}

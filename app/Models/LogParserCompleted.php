<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LogParserCompleted extends Model
{
    use SoftDeletes;

    public $table = 'log_parser_completed';
    protected $fillable = [
        'log_id',
        'line_number',
        'line_data',
    ];
}

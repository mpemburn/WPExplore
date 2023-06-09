<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CfLegacyApp extends Model
{
    use HasFactory;

    protected $fillable = [
        'server',
        'web_root',
        'index_url',
        'page_title',
        'redirect_url',
        'error_code',
    ];
    public $table = 'cold_fusion_legacy_apps';

}

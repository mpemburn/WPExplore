<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CfLegacyApp extends Model
{
    use HasFactory;

    const ERROR_CODES = [
        301 => '301 Moved',
        302 => '302 Gone ',
        401 => '401 Unauthorized',
        404 => '404 Not Found',
        410 => 'Gone',
        500 => '500 Fatal Error',
    ];

    protected $fillable = [
        'server',
        'web_root',
        'index_url',
        'page_title',
        'redirect_url',
        'error_code',
    ];
    public $table = 'cold_fusion_legacy_apps';

    public function getErrorCodeAttribute($code)
    {
        return self::ERROR_CODES[$code] ?? 'n/a';
    }

    public function getRedirectUrlAttribute($url)
    {
        return $url ?? 'n/a';
    }

    public function getPageTitleAttribute($title)
    {
        return str_replace(',', '-',$title) ?? 'n/a';
    }

}

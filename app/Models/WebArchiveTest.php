<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WebArchiveTest extends Model
{
    use HasFactory;

    public const ERROR_CODES = [
        0 => 'n/a',
        301 => '301 Moved',
        302 => '302 Gone ',
        401 => '401 Unauthorized',
        404 => '404 Not Found',
        410 => 'Gone',
        500 => '500 Fatal Error',
    ];

    protected $fillable = [
        'server',
        'category',
        'web_root',
        'index_url',
        'page_title',
        'redirect_url',
        'error_code',
    ];
    public $table = 'web_archive_test';

    public function getErrorCodeAttribute($code)
    {
        return self::ERROR_CODES[$code] ?? 'okay';
    }

    public function getWebRootAttribute($web_root)
    {
        return '=HYPERLINK("' . $web_root . '", "' . $web_root . '")';
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

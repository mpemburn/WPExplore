<?php

namespace App\Factories;

use App\Interfaces\FindableLink;
use App\Models\ClarkProductionLink;
use App\Models\WordpressProductionLink;
use App\Models\WordpressTestLink;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LinkFactory
{
    public static function build(string $env): FindableLink
    {
        switch ($env) {
            case 'wpprod':
                return new WordpressProductionLink();
            case 'wptest':
                return new WordpressTestLink();
            case 'clarkprod':
                return new ClarkProductionLink();
            default:
                throw new ModelNotFoundException('No valid Model specified by "' . $env . '"');
        }
    }
}

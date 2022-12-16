<?php

namespace App\Factories;

use App\Interfaces\FindableLink;
use App\Models\ClarkProductionLink;
use App\Models\WwwDevBrokenPage;
use App\Models\WordpressProductionLink;
use App\Models\WordpressTestLink;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LinkFactory
{
    public static function build(string $env): FindableLink
    {
        return match ($env) {
            'wpprod' => new WordpressProductionLink(),
            'wptest' => new WordpressTestLink(),
            'clarkprod' => new ClarkProductionLink(),
            'wwwdev' => new WwwDevBrokenPage(),
            default => throw new ModelNotFoundException('No valid Model specified by "' . $env . '"'),
        };
    }
}

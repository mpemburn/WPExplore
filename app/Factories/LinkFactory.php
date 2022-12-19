<?php

namespace App\Factories;

use App\Interfaces\FindableLink;
use App\Models\ClarkProductionLink;
use App\Models\DevBrokenPage;
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
            'wwwdev' => new DevBrokenPage(),
            default => throw new ModelNotFoundException('No valid Model specified by "' . $env . '"'),
        };
    }
}

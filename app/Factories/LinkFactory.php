<?php

namespace App\Factories;

use App\Interfaces\FindableLink;
use App\Models\ClarkNowBrokenPage;
use App\Models\ClarkProductionLink;
use App\Models\DevBrokenPage;
use App\Models\FutureProductionBrokenPage;
use App\Models\FutureTestBrokenPage;
use App\Models\NewsProductionBrokenPage;
use App\Models\NewsTestBrokenPage;
use App\Models\ProductionBrokenPage;
use App\Models\SitesProductionBrokenPage;
use App\Models\SitesTestBrokenPage;
use App\Models\TestingBrokenPage;
use App\Models\TrainingBrokenPage;
use App\Models\WordPressProductionBrokenPage;
use App\Models\WordpressProductionLink;
use App\Models\WordPressTestBrokenPage;
use App\Models\WordpressTestLink;
use App\Models\LocalBrokenPage;
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
            'wwwprod' => new ProductionBrokenPage(),
            'wwwtest' => new TestingBrokenPage(),
            'wwwtraining' => new TrainingBrokenPage(),
            'wordpressprod' => new WordPressProductionBrokenPage(),
            'wordpresstest' => new WordPressTestBrokenPage(),
            'newsprod' => new NewsProductionBrokenPage(),
            'newstest' => new NewsTestBrokenPage(),
            'sitesprod' => new SitesProductionBrokenPage(),
            'sitestest' => new SitesTestBrokenPage(),
            'futureprod' => new FutureProductionBrokenPage(),
            'futuretest' => new FutureTestBrokenPage(),
            'clarknow' => new ClarkNowBrokenPage(),
            'local' => new LocalBrokenPage(),
            default => throw new ModelNotFoundException('No valid Model specified by "' . $env . '"'),
        };
    }
}

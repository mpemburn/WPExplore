<?php

namespace App\Console\Commands;

use App\Services\ColdFusionLegacyAppService;
use Illuminate\Console\Command;

class TestCfUrls extends Command
{
    protected $signature = 'test:cf';

    public function handle()
    {
        (new ColdFusionLegacyAppService())->testAllUrls();
    }

}

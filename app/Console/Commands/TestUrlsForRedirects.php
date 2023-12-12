<?php

namespace App\Console\Commands;

use App\Models\CfLegacyApp;
use App\Services\ColdFusionLegacyAppService;
use DOMDocument;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RedirectMiddleware;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestUrlsForRedirects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redirect:test {--server=} {--file=} {--baseurl=} {--path=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sourceFile = $this->option('file');
        $baseUrl = $this->option('baseurl');
        $path = $this->option('path') ?? '';
        $server = $this->option('server');

        (new ColdFusionLegacyAppService())->testAndWrite(
            $sourceFile,
            $baseUrl,
            $server,
            $path
        );
        return Command::SUCCESS;
    }
}

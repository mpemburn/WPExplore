<?php

namespace App\Console\Commands;

use App\Models\CfmArchiveTest;
use App\Models\WebArchiveTest;
use App\Services\WebArchiveService;
use App\Services\WebTestService;
use Illuminate\Console\Command;

class TestWebUrls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:web {--server=} {--file=} {--baseurl=} {--path=}';

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
        $server = $this->option('server');
        $sourceFile = $this->option('file');
        $baseUrl = $this->option('baseurl');
        $path = $this->option('path') ?? '';

        (new WebArchiveService())
            ->setBaseUrl($baseUrl)
            ->setServer($server)
            ->setFilePath($path)
            ->setDataModel(new CfmArchiveTest())
            ->runTests();

        return Command::SUCCESS;
    }
}

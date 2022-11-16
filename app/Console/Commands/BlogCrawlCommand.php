<?php

namespace App\Console\Commands;

use App\Services\BlogCrawlerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BlogCrawlCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:crawl';

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
    public function handle(BlogCrawlerService $service)
    {
        $service->loadCrawlProcesses()->run();

        return Command::SUCCESS;
    }
}

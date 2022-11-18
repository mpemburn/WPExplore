<?php

namespace App\Console\Commands;

use App\Models\TestLink;
use App\Models\ProductionLink;
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
    protected $signature = 'blog:crawl {--env=}';

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
        $linkFinder = $this->option('env') === 'prod' ? new ProductionLink() : new TestLink();
        $service = new BlogCrawlerService($linkFinder);

        $service->loadCrawlProcesses(true)->run();

        return Command::SUCCESS;
    }
}

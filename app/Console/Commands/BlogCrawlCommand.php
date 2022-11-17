<?php

namespace App\Console\Commands;

use App\Models\TestImage;
use App\Models\ProductionImage;
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
        $imageFinder = $this->option('env') === 'prod' ? new ProductionImage() : new TestImage();
        $service = new BlogCrawlerService($imageFinder);

        $service->loadCrawlProcesses(true)->run();

        return Command::SUCCESS;
    }
}

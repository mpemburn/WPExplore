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
    protected $signature = 'blog:crawl {--env=} {--flush}';

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

        $flushData = $this->option('flush') ? true : false;

        if ($flushData) {
            $message = 'The --flush option will truncate the ' . $linkFinder->getTable() . ' table' . PHP_EOL;
            if (!$this->confirm($message . ' Do you wish to continue?', false)) {
                $this->info("Process terminated by user");

                return;
            }
        }

        $service = new BlogCrawlerService($linkFinder, $flushData);

        $echo = $this->option('verbose');

        $service->loadCrawlProcesses($echo)->run();

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Factories\LinkFactory;
use App\Interfaces\FindableLink;
use App\ObserverActions\BlogObserverAction;
use App\Services\BlogCrawlerService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PDOException;

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
        $echo = $this->option('verbose');
        $linkFinder = $this->getLinkFinder($this->option('env'));
        $action = new BlogObserverAction($linkFinder, $echo);

        $flushData = (bool)$this->option('flush');

        if ($flushData) {
            $message = 'The --flush option will truncate the ' . $linkFinder->getTable() . ' table' . PHP_EOL;
            if (!$this->confirm($message . ' Do you wish to continue?', false)) {
                $this->info("Process terminated by user");

                return Command::FAILURE;
            }
        }

        (new BlogCrawlerService($action, $flushData))
            ->loadCrawlProcesses($echo)->run();

        return Command::SUCCESS;
    }

    protected function getLinkFinder($env): ?FindableLink
    {
        try {
            return LinkFactory::build($env);
        } catch (PDOException $pdoex) {
            $this->info('Error: ' . $pdoex->getMessage());
            die();
        } catch (ModelNotFoundException $mnfex) {
            $this->info('Error: ' . $mnfex->getMessage());
            die();
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Factories\LinkFactory;
use App\Interfaces\FindableLink;
use App\Interfaces\ObserverAction;
use App\ObserverActions\BlogObserverAction;
use App\Services\BlogCrawlerService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PDOException;

abstract class CrawlCommand extends Command
{
    protected ObserverAction $observerAction;
    protected ?FindableLink $linkFinder;
    protected bool $echo = false;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $flushData = (bool)$this->option('flush');

        if ($flushData) {
            $message = 'The --flush option will truncate the ' . $this->linkFinder->getTable() . ' table' . PHP_EOL;
            if (!$this->confirm($message . ' Do you wish to continue?', false)) {
                $this->info("Process terminated by user");

                return Command::FAILURE;
            }
        }

        (new BlogCrawlerService($this->observerAction, $flushData))
            ->loadCrawlProcesses($this->echo)->run();

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

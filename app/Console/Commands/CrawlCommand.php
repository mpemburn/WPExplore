<?php

namespace App\Console\Commands;

use App\Factories\LinkFactory;
use App\Interfaces\FindableLink;
use App\Interfaces\ObserverAction;
use App\Models\BlogList;
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
        $topOnly = (bool)$this->option('top');
        $flushData = (bool)$this->option('flush');

        if ($topOnly) {
            $this->testTopLevelOnly($this->linkFinder);
            return Command::SUCCESS;
        }

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

    protected function testTopLevelOnly(FindableLink $finder): void
    {
        BlogList::where('site', $finder->getSite())->each(function ($blog) use ($finder) {
            if (! $blog->blog_url) {
                return;
            }
            $url = $finder->replaceBasePath($blog->blog_url);
            if ($this->echo) {
                echo 'Testing ' . $url . PHP_EOL;
            }

            $code = (new BlogCrawlerService($this->observerAction))->testUrl($url);

            if ($code !== 200) {
                echo 'Failed with ' . $code . ' error.' . PHP_EOL;
            }
        });
    }
}

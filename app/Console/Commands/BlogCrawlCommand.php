<?php

namespace App\Console\Commands;

use App\ObserverActions\BlogObserverAction;

class BlogCrawlCommand extends CrawlCommand
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
    protected $description = 'Crawls blogs to find broken links';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->echo = $this->option('verbose');
        $this->linkFinder = $this->getLinkFinder($this->option('env'));
        $this->observerAction = new BlogObserverAction($this->linkFinder, $this->echo);

        return parent::handle();
    }
}

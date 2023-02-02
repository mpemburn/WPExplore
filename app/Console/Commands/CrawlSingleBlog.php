<?php

namespace App\Console\Commands;

use App\ObserverActions\BlogObserverAction;
use App\ObserverActions\BrokenPageObserverAction;
use App\Services\BlogCrawlerService;
use Illuminate\Console\Command;

class CrawlSingleBlog extends CrawlCommand
{
    protected string $url;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blog:single {--env=} {--url=}';

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
        $this->echo = $this->option('verbose');
        $this->url = $this->option('url');
        $this->linkFinder = $this->getLinkFinder($this->option('env'));
        $this->observerAction = new BrokenPageObserverAction($this->linkFinder, $this->echo, false);

        (new BlogCrawlerService($this->observerAction))->fetchContent(0, $this->url);

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Interfaces\ObserverAction;
use App\ObserverActions\BrokenPageObserverAction;

class ScanForBrokenPagesCommand extends CrawlCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scan:pages {--env=} {--flush}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans for broken pages';

    public function handle()
    {
        $this->echo = $this->option('verbose');
        $this->linkFinder = $this->getLinkFinder($this->option('env'));
        $this->observerAction = new BrokenPageObserverAction($this->linkFinder, $this->echo);

        return parent::handle();
    }

}

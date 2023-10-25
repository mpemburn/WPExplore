<?php

namespace App\Console\Commands;

use App\ObserverActions\BrokenPageObserverAction;

class ScanForBrokenPagesCommand extends CrawlCommand
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans for broken pages';

    /*
    protected $signature = 'scan:pages {--env=} {--flush} {--resume_at=} {--top} {--fatal} {--nopersist}}';
    */

    public function handle()
    {
        $this->echo = $this->option('verbose') ? true : false;
        $this->linkFinder = $this->getLinkFinder($this->option('env'));
        if ($this->linkFinder) {
            $this->observerAction = new BrokenPageObserverAction($this->linkFinder, $this->echo);
        }

        return parent::handle();
    }
}

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
    protected $signature = 'scan:pages {--env=} {--flush} {--top} {--fatal}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scans for broken pages';

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

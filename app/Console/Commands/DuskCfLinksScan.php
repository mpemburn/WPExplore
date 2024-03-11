<?php

namespace App\Console\Commands;

use App\Models\CfLinks;
use App\Services\BrowserService;
use Illuminate\Console\Command;

class DuskCfLinksScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cf:browse';

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
        $service = new BrowserService();
        $links = CfLinks::whereNotNull('redirect')
            ->orderBy('category')
            ->limit(5)
            ->get();
        $links->each(function ($link) use ($service) {
            $server = $link->server;
            $url = $link->url;
            $title = $service->scrapeElement($url, 'title');
            echo '"' . $url . '","' . $title . '"' . PHP_EOL;
        });

        return Command::SUCCESS;
    }
}

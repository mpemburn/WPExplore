<?php

namespace App\Console\Commands;

use App\Facades\Curl;
use App\Observers\UrlObserver;
use GuzzleHttp\RequestOptions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\Crawler\Crawler;

class TestListOfUrlsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crawl:list';

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
        $file = Storage::path('www sites on clarku-subsite.csv');
        $csv = file_get_contents($file);

        collect(explode("\n", $csv))->each(function ($url) {
            echo $url . PHP_EOL;
            if (! Curl::testUrl($url)) {
                echo 'Error!' . PHP_EOL;
            }
//            $options = [RequestOptions::ALLOW_REDIRECTS => true, RequestOptions::TIMEOUT => 30];
//
//            //# initiate crawler
//            Crawler::create($options)
//                ->acceptNofollowLinks()
//                ->ignoreRobots()
//                ->setCrawlObserver(new UrlObserver())
//                ->setMaximumResponseSize(1024 * 1024 * 2) // 2 MB maximum
//                ->setDelayBetweenRequests(500)
//                ->startCrawling($url);
        });
        return Command::SUCCESS;
    }
}

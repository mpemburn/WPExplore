<?php

namespace App\Http\Controllers;

use App\Services\BlogCrawlerService;

class BlogCrawlerController extends Controller {

    public function crawl(BlogCrawlerService $service)
    {
        $service->loadCrawlProcesses()->run();
    }
}

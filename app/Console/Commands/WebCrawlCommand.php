<?php
namespace App\Console\Commands;
use App\Services\BlogService;
use App\Services\WebCrawlerService;
use Illuminate\Console\Command;

class WebCrawlCommand extends Command
{
    protected $signature = 'web:crawl {--url=} {--find=}';
    public function handle()
    {
        $url = $this->option('url');
        $find = $this->option('find');

        (new WebCrawlerService())->findInBlogs($find, true);

        return Command::SUCCESS;
    }
}

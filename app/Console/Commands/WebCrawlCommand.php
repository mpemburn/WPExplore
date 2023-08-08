<?php
namespace App\Console\Commands;
use App\Services\BlogService;
use App\Services\WebCrawlerService;
use Illuminate\Console\Command;

class WebCrawlCommand extends Command
{
    protected $signature = 'web:crawl {--url=} {--find=} {--resume=}';
    public function handle(): int
    {
        $url = $this->option('url');
        $find = $this->option('find');
        $resumeAt = $this->option('resume');

        (new WebCrawlerService())
            ->shouldResume($resumeAt)
            ->findInBlogs($find, true);

        return Command::SUCCESS;
    }
}

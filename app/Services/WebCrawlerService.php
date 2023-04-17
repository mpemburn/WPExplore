<?php
namespace App\Services;

use App\Models\BlogList;
use App\Observers\WebObserver;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;
use Spatie\Crawler\Crawler;

class WebCrawlerService
{
    protected Collection $processes;

    public function __construct()
    {
        $this->processes = collect();
    }

    public function findInBlogs(string $find, bool $echo = false): self
    {
        $blogs = $this->getBlogList();

        $blogs->each(function ($blog) use ($find, $echo) {
            if (str_contains($blog->blog_url, 'clarknow')) {
                return;
            }
            $blogUrl = str_replace('www.', 'www.training.', $blog->blog_url);

            if ($echo) {
                echo  'Adding ' .$blogUrl . PHP_EOL;
            }

            $this->processes->push($this->fetchContent($blogUrl, $find, $echo));
        });

        return $this;

    }

    public function fetchContent(string $url, string $find, bool $echo = false) {
        $options = [RequestOptions::ALLOW_REDIRECTS => true, RequestOptions::TIMEOUT => 30];

        //# initiate crawler
        Crawler::create($options)
            ->acceptNofollowLinks()
            ->ignoreRobots()
            ->setCrawlObserver(new WebObserver($find, $echo))
            ->setMaximumResponseSize(1024 * 1024 * 2) // 2 MB maximum
            ->setDelayBetweenRequests(500)
            ->startCrawling($url);
        return true;
    }

    protected function getBlogList()
    {
        return BlogList::where('site', 'www');
    }
}

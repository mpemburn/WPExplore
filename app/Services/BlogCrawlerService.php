<?php

namespace App\Services;

use App\Interfaces\FindableLink;
use App\Interfaces\ObserverAction;
use App\Models\Blog;
use App\Models\BlogList;
use App\Models\Option;
use App\Observers\BlogObserver;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;
use Spatie\Async\Pool;
use Spatie\Crawler\Crawler;

class BlogCrawlerService
{
    protected Collection $processes;
    protected FindableLink $linkFinder;
    protected ObserverAction $observerAction;

    public function __construct(ObserverAction $observerAction, bool $flushData = false)
    {
        $this->linkFinder = $observerAction->getLinkFinder();

        $this->processes = collect();
        if ($flushData) {
            $this->truncate($this->linkFinder);
        }
        $this->observerAction = $observerAction;
    }

    public function loadCrawlProcesses(bool $echo = false): self
    {
        $finderClass = $this->linkFinder::class;
        $finder = new $finderClass();
        $blogs = BlogList::where('site', $finder->getSite());

        $blogs->each(function ($blog) use ($finder, $echo) {
            $blogUrl = $finder->replaceBasePath($blog['blog_url']);

            if ($echo) {
                echo  'Adding ' .$blogUrl . PHP_EOL;
            }

            $this->processes->push($this->fetchContent((int)$blog['blog_id'], $blog['blog_url'], $echo));
        });

        return $this;
    }

    /**
     * Crawl the website content.
     * @return true
     */
    public function fetchContent(int $blogId, string $url, bool $echo = false) {
        $options = [RequestOptions::ALLOW_REDIRECTS => true, RequestOptions::TIMEOUT => 30];
        // Get HTTP Basic Auth username and password if available
        $options = $this->linkFinder->getAuth($options);

        //# initiate crawler
        Crawler::create($options)
            ->acceptNofollowLinks()
            ->ignoreRobots()
            ->setCrawlObserver(new BlogObserver($blogId, $this->observerAction))
            ->setMaximumResponseSize(1024 * 1024 * 2) // 2 MB maximum
            ->setDelayBetweenRequests(500)
            ->startCrawling($url);
        return true;
    }

    public function run()
    {
        $pool = Pool::create();

        $this->processes->each(function ($process) use (&$pool) {
            $pool->add(function () use ($process) {
                // Do a thing
            })->then(function ($output) {
                // Handle success
            })->catch(function (Throwable $exception) {
                // Handle exception
            });
        });

        $pool->wait();
    }

    public function urlExists($url): bool
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($code == 200);
    }

    protected function truncate(FindableLink $linkFinder)
    {
        $class = $linkFinder::class;
        $class::query()->truncate();
    }

}

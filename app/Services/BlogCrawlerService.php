<?php

namespace App\Services;

use App\Interfaces\FindableLink;
use App\Interfaces\ObserverAction;
use App\Models\Blog;
use App\Models\WordpressTestLink;
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
        $blogs = Blog::where('archived', 0);

        $blogs->each(function ($blog) use ($echo) {
            // Make sure we're not duplicating a blog
            $finderClass = $this->linkFinder::class;
            $finder = new $finderClass();
            $foundCount = $finder->where('blog_id', $blog->blog_id)->count();

            if ($blog->blog_id < 2 || $foundCount > 0) {
                return;
            }

            $options = (new Option())->setTable('wp_'. $blog->blog_id .'_options')
                ->whereIn('option_name', ['siteurl'])
                ->orderBy('option_name');

            $options->each(function (Option $option) use ($finder, $blog, $echo) {
                $blogName = $finder->replaceBasePath($option->option_value);

                if ($echo) {
                    echo  'Adding ' .$blogName . PHP_EOL;
                }

                $this->processes->push($this->fetchContent((int)$blog->blog_id, $blogName, $echo));
            });
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
            ->setDelayBetweenRequests(1000)
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

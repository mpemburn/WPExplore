<?php

namespace App\Services;

use App\Interfaces\FindableLink;
use App\Interfaces\ObserverActionInterface;
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
    protected ObserverActionInterface $observerAction;
    protected bool $resume = false;
    protected int $resumeAt = 0;

    public function __construct(ObserverActionInterface $observerAction, bool $flushData = false, int $resumeAt = 0)
    {
        $this->linkFinder = $observerAction->getLinkFinder();

        $this->processes = collect();
        if ($flushData) {
            $this->truncate($this->linkFinder);
            $this->resume = false;
        }

        $this->observerAction = $observerAction;
        $this->resumeAt = $resumeAt;
    }

    public function loadCrawlProcesses(bool $echo = false): self
    {
        $finderClass = $this->linkFinder::class;
        $finder = new $finderClass();
        $blogs = $this->resume
            ? $this->getRemainingBlogs($finder)
            : BlogList::where('site', $finder->getSite())->where('deprecated', 0);

        $blogs->each(function ($blog) use ($finder, $echo) {
            $blogUrl = $finder->replaceBasePath($blog['blog_url']);

            if ($echo) {
                echo  'Adding ' .$blogUrl . PHP_EOL;
            }

            $this->processes->push($this->fetchContent((int)$blog['blog_id'], $blogUrl, $echo));
        });

        return $this;
    }

    protected function getRemainingBlogs(FindableLink $finder): Collection
    {
        if ($this->resumeAt > 0) {
            $remainingBlogs = BlogList::where('site', $finder->getSite())
                ->where('blog_id', '>=', $this->resumeAt)
                ->get();
        } else {
            // Get the last entry in the $finder table
            $last = $finder::query()->latest()->first();
            // Use the ID from this get the most recently crawled blog
            $lastBlog = BlogList::where('site', $finder->getSite())
                ->where('blog_id', $last->blog_id);
            // Get the list of all blogs from the last crawled to the end
            $remainingBlogs = BlogList::where('id', '>=', $lastBlog->id)->get();
            // Delete the records from the last blog crawled so that they aren't duplicated
            $finder::query()
                ->where('blog_id', $last->blog_id)
                ->delete();
        }

        return $remainingBlogs;
    }

    /**
     * Crawl the website content.
     * @return true
     */

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

    public function urlExists(string $url): bool
    {
        return ($this->testUrl($url) === 200);
    }

    public function testUrl(string $url, ?string $username = null, ?string $password = null): int
    {
        return (new UrlService())->testUrl($url, $username, $password);
    }

    protected function truncate(FindableLink $linkFinder): void
    {
        $class = $linkFinder::class;
        $class::query()->truncate();
    }

}

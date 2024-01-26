<?php

namespace App\Services;

use App\Facades\Curl;
use App\Facades\Database;
use App\Interfaces\FindableLink;
use App\Interfaces\ObserverActionInterface;
use App\Models\Blog;
use App\Models\BlogList;
use App\Models\Option;
use App\Observers\BlogObserver;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Spatie\Async\Pool;
use Spatie\Crawler\Crawler;
use Throwable;

class BlogCrawlerService
{
    protected Collection $processes;
    protected Collection $blogs;
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

    public function setIncludeList(): self
    {
        $file = Storage::path('www_sites_with_clarku-subsite.csv');
        $csv = file_get_contents($file);
        $this->blogs = collect();

        collect(explode("\n", $csv))->each(function ($line) {
            $parts = explode(',', $line);

            $this->blogs->push(collect([
                'blog_id' => $parts[0],
                'blog_url' => $parts[1],
            ]));
        });

        return $this;
    }

    public function loadCrawlProcesses(bool $echo = false): self
    {
        $finderClass = $this->linkFinder::class;
        $finder = new $finderClass();
        $blogs = $this->resume
            ? $this->getRemainingBlogs($finder)
            : $this->getBlogsList($finder);

        $blogs->each(function ($blog) use ($finder, $echo) {
            $blogUrl = $finder->replaceBasePath($blog['blog_url']);

            if ($echo) {
                echo  'Adding ' .$blogUrl . PHP_EOL;
            }

            $this->processes->push($this->fetchContent((int)$blog['blog_id'], $blogUrl, $echo));
        });

        return $this;
    }

    protected function getBlogsList(FindableLink $finder)
    {
        if ($this->blogs) {
            return $this->blogs;
        }

        if (! $finder->sourceDb) {
            return BlogList::where('site', $finder->getSite())
                ->where('deprecated', 0)
                ->get();
        }

        Database::setDb($finder->sourceDb);
        $blogList = collect();
        (new BlogService())->getActiveBlogs()
            ->each(function ($blog) use (&$blogList) {
                $blogList->push([
                    'blog_id' => $blog['blog_id'],
                    'blog_url' => $blog['siteurl'],
                ]);
            });
        Database::setDb(env('DB_DATABASE'));


        return $blogList;
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
    public function fetchContent(int $blogId, string $url, bool $echo = false) {
        echo $url . ' Found' . PHP_EOL;

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

    public function urlExists(string $url): bool
    {
        return ($this->testUrl($url) === 200);
    }

    public function testUrl(string $url, ?string $username = null, ?string $password = null): int
    {
        return Curl::testUrl($url, $username, $password);
    }

    protected function truncate(FindableLink $linkFinder): void
    {
        $class = $linkFinder::class;
        $class::query()->truncate();
    }

}

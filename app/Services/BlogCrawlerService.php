<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\FoundImage;
use App\Models\Option;
use App\Observers\BlogObserver;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Collection;
use Spatie\Async\Pool;
use Spatie\Crawler\Crawler;

class BlogCrawlerService
{
    protected Collection $processes;

    public function __construct()
    {
        $this->processes = collect();
    }

    public function loadCrawlProcesses(bool $echo = false): self
    {
        $blogs = Blog::all();

        $blogs->each(function ($blog) use ($echo) {
            $foundCount = FoundImage::where('blog_id', $blog->blog_id)->count();
            if ($blog->blog_id < 2 || $foundCount > 0) {
                return;
            }

            $options = (new Option())->setTable('wp_'. $blog->blog_id .'_options')
                ->whereIn('option_name', ['siteurl'])
                ->orderBy('option_name');

            $options->each(function (Option $option) use ($blog, $echo) {
                $blogName = $option->option_value;
                if ($echo) {
                    echo $blogName . ' added' . PHP_EOL;
                }
                $this->processes->push($this->fetchContent((int)$blog->blog_id, $blogName));
            });
        });

        return $this;
    }

    /**
     * Crawl the website content.
     * @return true
     */
    public function fetchContent(int $blogId, string $url) {
        //# initiate crawler
        Crawler::create([RequestOptions::ALLOW_REDIRECTS => true, RequestOptions::TIMEOUT => 30])
            ->acceptNofollowLinks()
            ->ignoreRobots()
            ->setCrawlObserver(new BlogObserver($blogId))
            ->setMaximumResponseSize(1024 * 1024 * 2) // 2 MB maximum
            ->setTotalCrawlLimit(25) // limit defines the maximal count of URLs to crawl
            ->setDelayBetweenRequests(100)
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


}

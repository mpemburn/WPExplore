<?php

namespace App\Services;

use App\Interfaces\FindableLink;
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

    public function __construct(FindableLink $linkFinder, bool $flushData = false)
    {
        $this->processes = collect();
        $this->linkFinder = $linkFinder;
        if ($flushData) {
            $this->truncate($linkFinder);
        }
    }

    public function loadCrawlProcesses(bool $echo = false): self
    {
        $blogs = Blog::where('archived', 0);

        $blogs->each(function ($blog) use ($echo) {
            // Make sure we're not duplicating a blog
            $finderClass = get_class($this->linkFinder);
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
        //# initiate crawler
        Crawler::create([RequestOptions::ALLOW_REDIRECTS => true, RequestOptions::TIMEOUT => 30])
            ->acceptNofollowLinks()
            ->ignoreRobots()
            ->setCrawlObserver(new BlogObserver($blogId, new $this->linkFinder(), $echo))
            ->setMaximumResponseSize(1024 * 1024 * 2) // 2 MB maximum
//            ->setTotalCrawlLimit(25) // limit defines the maximal count of URLs to crawl
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
        $class = get_class($linkFinder);
        $class::query()->truncate();
    }

}

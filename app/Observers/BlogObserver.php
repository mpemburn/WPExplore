<?php

namespace App\Observers;

use App\Interfaces\ObserverActionInterface;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class BlogObserver extends CrawlObserver
{
    protected ?string $blogRoot = null;
    protected int $blogId;

    public function __construct(int $blogId, protected ObserverActionInterface $observerAction)
    {
        $this->content = collect();
        $this->blogId = $blogId;
        $this->observerAction->setBlogId($this->blogId);
    }

    /**
     * Called when the crawler will crawl the url.
     *
     */
    public function willCrawl(UriInterface $url): void
    {
        $this->blogRoot = $this->blogRoot ?: $url;
        Log::info('willCrawl', ['url' => $url]);
    }

    /**
     * Called when the crawler has crawled the given url successfully.
     *
     */
    public function crawled(
        UriInterface      $url,
        ResponseInterface $response,
        ?UriInterface     $foundOnUrl = null
    ): void
    {
        $this->observerAction->setBlogRoot($this->blogRoot)
            ->act($url, $response, $foundOnUrl);
    }

    /**
     * Called when the crawler had a problem crawling the given url.
     *
     */
    public function crawlFailed(
        UriInterface     $url,
        RequestException $requestException,
        ?UriInterface    $foundOnUrl = null
    ): void
    {
        $this->observerAction->recordFailure($url, $requestException->getMessage());
    }

    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling(): void
    {
        if ($this->observerAction->verbose()) {
            echo 'Done!' . PHP_EOL;
        }
    }
}

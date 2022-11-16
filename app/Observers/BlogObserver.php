<?php

namespace App\Observers;

use App\Models\FoundImage;
use DOMDocument;
use Illuminate\Support\Collection;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class BlogObserver extends CrawlObserver
{
    protected int $blogId;
    protected Collection $content;

    public function __construct(int $blogId)
    {
        $this->blogId = $blogId;
        $this->content = collect();
    }

    /**
     * Called when the crawler will crawl the url.
     *
     * @param \Psr\Http\Message\UriInterface $url
     */
    public function willCrawl(UriInterface $url): void
    {
        Log::info('willCrawl', ['url' => $url]);
    }

    /**
     * Called when the crawler has crawled the given url successfully.
     *
     * @param \Psr\Http\Message\UriInterface $url
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Psr\Http\Message\UriInterface|null $foundOnUrl
     */
    public function crawled(
        UriInterface      $url,
        ResponseInterface $response,
        ?UriInterface     $foundOnUrl = null
    ): void
    {
        $images = collect();

        $doc = new DOMDocument();
        $body = $response->getBody();

        if (strlen($body) < 1) {
            return;
        }

        @$doc->loadHTML($body);
        //# save HTML
        $content = $doc->saveHTML();

        if (strpos($content, '<img') !== false) {
            $regexp = '<img[^>]+src=(?:\"|\')\K(.[^">]+?)(?=\"|\')';

            if (preg_match_all("/$regexp/", $content, $matches, PREG_SET_ORDER) && $matches) {
                foreach ($matches as $match) {
                    $image = current($match);
                    // Get only the images moved by multisite upgrade
                    if (strpos($image, 'wp-content/uploads/sites') === false) {
                        continue;
                    }
                    $images->push($image);
                    FoundImage::create([
                        'blog_id' => $this->blogId,
                        'page_url' => $url,
                        'image_url' => $image,
                        'original_exists' => false,
                        'success' => false,
                    ]);
                }
            }
        }

        $this->content = $this->content->merge($images);
    }

    /**
     * Called when the crawler had a problem crawling the given url.
     *
     * @param \Psr\Http\Message\UriInterface $url
     * @param \GuzzleHttp\Exception\RequestException $requestException
     * @param \Psr\Http\Message\UriInterface|null $foundOnUrl
     */
    public function crawlFailed(
        UriInterface     $url,
        RequestException $requestException,
        ?UriInterface    $foundOnUrl = null
    ): void
    {
        Log::error('crawlFailed', ['url' => $url, 'error' => $requestException->getMessage()]);
    }

    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling(): void
    {
//        Log::info("finishedCrawling");
//        !d("finishedCrawling");
    }
}

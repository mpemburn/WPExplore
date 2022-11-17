<?php

namespace App\Observers;

use App\Interfaces\FindableImage;
use App\Services\BlogCrawlerService;
use DOMDocument;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class BlogObserver extends CrawlObserver
{
    protected int $blogId;
    protected FindableImage $imageFinder;

    public function __construct(int $blogId, FindableImage $imageFinder)
    {
        $this->blogId = $blogId;
        $this->content = collect();
        $this->imageFinder = $imageFinder;
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
        if (strpos($url, $this->imageFinder->getBlogBasePath()) === false) {
            return;
        }

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
                    // Get only the images in the defined basePath
                    if (strpos($image, $this->imageFinder->getImageBasePath()) === false) {
                        continue;
                    }
                    $found = (new BlogCrawlerService($this->imageFinder))->urlExists($image);

                    $finder = new $this->imageFinder();
                    $finder->create([
                        'blog_id' => $this->blogId,
                        'page_url' => $url,
                        'image_url' => $image,
                        'found' => $found,
                    ]);
                }
            }
        }
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
        echo '...Done!' . PHP_EOL;
    }
}

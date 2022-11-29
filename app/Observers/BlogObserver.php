<?php

namespace App\Observers;

use App\Interfaces\FindableLink;
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
    protected ?string $blogRoot = null;
    protected FindableLink $linkFinder;
    protected bool $echo;

    public function __construct(int $blogId, FindableLink $linkFinder, bool $echo = false)
    {
        $this->blogId = $blogId;
        $this->content = collect();
        $this->linkFinder = $linkFinder;
        $this->echo = $echo;
    }

    /**
     * Called when the crawler will crawl the url.
     *
     * @param \Psr\Http\Message\UriInterface $url
     */
    public function willCrawl(UriInterface $url): void
    {
        $this->blogRoot = $this->blogRoot ?: $url;
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
        if (strpos($url, $this->linkFinder->getBlogBasePath()) === false) {
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

        $found = false;
        // Search for image links
        if (strpos($content, '<img') !== false) {
            $regexp = '<img[^>]+src=(?:\"|\')\K(.[^">]+?)(?=\"|\')';
            $this->addLink($regexp, $content, $url);
            $found = true;
        }
        // Search for .pdf links
        if (strpos($content, '.pdf') !== false) {
            $regexp = '<a[^>]+href=(?:\"|\')\K(.[^">]+?pdf)(?=\"|\')';
            $this->addLink($regexp, $content, $url);
            $found = true;
        }

        if (! $found && $this->echo) {
            echo 'not found' . PHP_EOL;
        }
    }

    protected function addLink(string $regexp, string $content, string $url): void
    {
        if (preg_match_all("/$regexp/", $content, $matches, PREG_SET_ORDER) && $matches) {
            foreach ($matches as $match) {
                $link = current($match);
                // If the link doesn't belong to this blog, skip it
                if (strpos($link, $this->blogRoot) === false) {
                    continue;
                }

                $found = (new BlogCrawlerService($this->linkFinder))->urlExists($link);

                $finder = new $this->linkFinder();
                $finder->create([
                    'blog_id' => $this->blogId,
                    'page_url' => $url,
                    'link_url' => $link,
                    'found' => $found,
                ]);

                if ($this->echo) {
                    echo '.';
                }

                return;
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
        if ($this->echo) {
            echo 'Done!' . PHP_EOL;
        }
    }
}

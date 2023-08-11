<?php

namespace App\Observers;

use App\Models\FoundText;
use DOMDocument;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class WebObserver extends CrawlObserver
{
    protected string $find;
    protected bool $echo;

    public function __construct(string $find, bool $echo = false)
    {
        $this->find = $find;
        $this->echo = $echo;
    }

    /**
     * Called when the crawler will crawl the url.
     *
     */
    public function willCrawl(UriInterface $url): void
    {
        if ($this->echo) {
            echo 'Testing: ' . $url . PHP_EOL;
        }
        if (str_contains($url, $this->find)) {
            echo '......Found: ' . $url . PHP_EOL;
            $foundText = new FoundText();
            $foundText->create([
                'search_string' => $this->find,
                'found_url' => $url,
                'found_in' => 'url',
            ]);
        }
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
        $doc = new DOMDocument();
        $body = $response->getBody();

        if (strlen($body) < 1) {
            return;
        }

        @$doc->loadHTML($body);
        //# save HTML
        $content = $doc->saveHTML();
        if (str_contains($content, $this->find)) {
            echo '......Found at: ' . $url . PHP_EOL;
            $foundText = new FoundText();
            $foundText->create([
                'search_string' => $this->find,
                'found_url' => $url,
                'found_in' => 'content',
            ]);
        }
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
        echo 'crawlFailed: ' . $url . PHP_EOL;
    }

    /**
     * Called when the crawl has ended.
     */
    public function finishedCrawling(): void
    {
    }
}

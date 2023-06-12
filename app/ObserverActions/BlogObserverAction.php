<?php

namespace App\ObserverActions;

use App\Interfaces\FindableLink;
use App\Interfaces\ObserverActionInterface;
use App\Services\BlogCrawlerService;
use DOMDocument;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class BlogObserverAction extends ObserverAction
{
    public function act(
        UriInterface      $url,
        ResponseInterface $response,
        ?UriInterface     $foundOnUrl = null
    ): void
    {

        if (! str_contains($url, 'https://' . $this->linkFinder->getSite())) {
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

        // Fix the URL if necessary
        $url = $this->linkFinder->replaceBasePath($url->__toString());

        // Search for image links
        if (str_contains($content, '<img')) {
            $regexp = '<img[^>]+src=(?:\"|\')\K(.[^">]+?)(?=\"|\')';
            $this->addLink($regexp, $content, $url);
        }
        // Search for .pdf links
        if (str_contains($content, '.pdf')) {
            $regexp = '<a[^>]+href=(?:\"|\')\K(.[^">]+?pdf)(?=\"|\')';
            $this->addLink($regexp, $content, $url);
        }
    }

    protected function addLink(string $regexp, string $content, string $url): void
    {
        if (preg_match_all("/$regexp/", $content, $matches, PREG_SET_ORDER) && $matches) {
            foreach ($matches as $match) {
                $link = current($match);
                // If the link doesn't belong to this blog, skip it
                if (!str_contains($link, $this->blogRoot) && ! $this->linkFinder->foundInAlternateImagePath($link)) {
                    continue;
                }

                if ($this->linkFinder->where('link_url', $link)->exists()) {
                    continue;
                }

                $found = (new BlogCrawlerService($this))->urlExists($link);

                $finder = new $this->linkFinder();

                if ($this->persist) {
                    $finder->create([
                        'blog_id' => $this->blogId,
                        'page_url' => $url,
                        'link_url' => $link,
                        'found' => $found,
                    ]);
                }

                if ($this->echo) {
                    echo '.';
                }

                return;
            }
        }
    }

    public function recordFailure(string $url, string $message): void
    {
        Log::error('crawlFailed', [$url => $url, $message]);
    }
}

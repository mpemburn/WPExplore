<?php

namespace App\ObserverActions;

use App\Interfaces\FindableLink;
use App\Interfaces\ObserverActionInterface;
use App\Services\BlogCrawlerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class BrokenPageObserverAction extends ObserverAction implements ObserverActionInterface
{
    public function act(
        UriInterface      $url,
        ResponseInterface $response,
        ?UriInterface     $foundOnUrl = null
    ): void
    {
        $url = $this->linkFinder->replaceBasePath($url->__toString());

        if (! $this->linkFinder->matchesBasePath($url) || $this->linkFinder->urlExists($url)) {
            return;
        }

        if ($this->echo) {
            echo 'Testing...' . $url . PHP_EOL;
        }

        $result = (new BlogCrawlerService($this))->testUrl($url);

        $linkFinder = new $this->linkFinder();

        if ($this->persist) {
            $linkFinder->create([
                'blog_id' => $this->blogId,
                'page_url' => $url,
                'error' => $result === 200 ? 'success' : $result . ' Error',
            ]);
        }
    }

    public function recordFailure(string $url, string $message): void
    {
        if (! $this->linkFinder->matchesBasePath($url)) {
            return;
        }

        $result = preg_match('/(.*)(resulted in a `)(.*)(` response)(:)/', $message, $matches);
        $error = $result ? $matches[3] : substr($message, 0, 499);

        if ($this->persist) {
            $linkFinder = new $this->linkFinder();
            $linkFinder->create([
                'blog_id' => $this->blogId,
                'page_url' => $url,
                'error' => $error,
            ]);
        }

        if ($this->echo) {
            echo 'ERROR: ' . $url . ' -- ' . $error . PHP_EOL;
        }
    }
}

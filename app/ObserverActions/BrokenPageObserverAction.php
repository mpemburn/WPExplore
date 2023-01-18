<?php

namespace App\ObserverActions;

use App\Interfaces\FindableLink;
use App\Interfaces\ObserverAction;
use App\Services\BlogCrawlerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class BrokenPageObserverAction implements ObserverAction
{
    protected FindableLink $linkFinder;
    protected int $blogId;
    protected string $blogRoot;
    protected bool $echo;

    public function __construct(FindableLink $linkFinder, bool $echo = false)
    {
        $this->linkFinder = $linkFinder;
        $this->echo = $echo;
    }

    public function setBlogId(int $blogId): self
    {
        $this->blogId = $blogId;

        return $this;
    }

    public function setBlogRoot(string $blogRoot): self
    {
        $this->blogRoot = $blogRoot;

        return $this;
    }

    public function verbose(): bool
    {
        return $this->echo;
    }

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
        $linkFinder->create([
            'blog_id' => $this->blogId,
            'page_url' => $url,
            'error' => $result === 200 ? 'success' : $result . ' Error',
        ]);
    }

    public function getLinkFinder(): FindableLink
    {
        return $this->linkFinder;
    }

    public function recordFailure(string $url, string $message): void
    {
        if (! $this->linkFinder->matchesBasePath($url)) {
            return;
        }

        $result = preg_match('/(.*)(resulted in a `)(.*)(` response)(:)/', $message, $matches);
        $error = $result ? $matches[3] : substr($message, 0, 499);

        $linkFinder = new $this->linkFinder();
        $linkFinder->create([
            'blog_id' => $this->blogId,
            'page_url' => $url,
            'error' => $error,
        ]);

        if ($this->echo) {
            echo 'ERROR: ' . $url . ' -- ' . $error . PHP_EOL;
        }
    }
}

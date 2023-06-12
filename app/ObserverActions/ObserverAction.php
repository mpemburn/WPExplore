<?php

namespace App\ObserverActions;

use App\Interfaces\FindableLink;
use App\Interfaces\ObserverActionInterface;
use App\Services\BlogCrawlerService;
use DOMDocument;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

abstract class ObserverAction implements ObserverActionInterface
{
    protected FindableLink $linkFinder;
    protected int $blogId;
    protected string $blogRoot;
    protected bool $echo;
    protected bool $persist = true;

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

    public function persist(bool $shouldPersist): void
    {
        $this->persist = $shouldPersist;
    }

    public function getLinkFinder(): FindableLink
    {
        return $this->linkFinder;
    }
}

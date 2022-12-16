<?php

namespace App\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

interface ObserverAction
{
    public function act(
        UriInterface      $url,
        ResponseInterface $response,
        ?UriInterface     $foundOnUrl = null
    ): void;
    public function setBlogId(int $blogId): self;
    public function setBlogRoot(string $blogRoot): self;
    public function verbose(): bool;
}

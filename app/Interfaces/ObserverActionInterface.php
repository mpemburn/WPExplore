<?php

namespace App\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

interface ObserverActionInterface
{
    public function act(
        UriInterface      $url,
        ResponseInterface $response,
        ?UriInterface     $foundOnUrl = null
    ): void;
    public function setBlogId(int $blogId): self;
    public function setBlogRoot(string $blogRoot): self;
    public function recordFailure(string $url, string $message): void;
    public function verbose(): bool;
    public function persist(bool $shouldPersist): void;
}

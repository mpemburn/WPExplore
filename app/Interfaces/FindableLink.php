<?php

namespace App\Interfaces;

/**
 * @method where(string $string, $blog_id)
 */
interface FindableLink
{
    public function getBlogBasePath(): string;
    public function foundInAlternateImagePath(string $path): bool;
    public function replaceBasePath(string $url): string;
    public function matchesBasePath(string $url): bool;
    public function urlExists(string $url): bool;
    public function getAuth(array $options): array;
}

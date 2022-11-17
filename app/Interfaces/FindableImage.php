<?php

namespace App\Interfaces;

/**
 * @method where(string $string, $blog_id)
 */
interface FindableImage
{
    public function getBlogBasePath(): string;
    public function getImageBasePath(): string;
}

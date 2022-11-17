<?php

namespace App\Interfaces;

interface FindableImage
{
    public function getBlogBasePath(): string;
    public function getImageBasePath(): string;
}

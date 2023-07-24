<?php

namespace App\Services;
class FileService
{
    static function toArray(string $filename): ?array
    {
        if (file_exists($filename)) {
            $contents = file_get_contents($filename);

            return explode("\n", $contents);
        }

        return null;
    }
}

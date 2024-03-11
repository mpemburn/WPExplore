<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use function Psy\debug;

class Reader
{
    public function contents(string $filename): ?string
    {
        if (file_exists($filename)) {
            return file_get_contents($filename);
        }

        return null;
    }

    public function getContentsAsArray(string $filename): ?array
    {
        $contents = $this->contents($filename);

        if (! $contents) {
            return null;
        }
        return explode("\n", $contents);
    }
}

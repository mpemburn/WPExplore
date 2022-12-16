<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class BugScanService
{
    public function parseDeprecationScanJSON(string $filename, string $path = '/public/'): Collection
    {
        $file = Storage::path($path . $filename);

        if (file_exists($file)) {
            $contents = file_get_contents($file);
            $array = json_decode($contents, true);
            $plugins = [];
            collect($array['problems'])->each(function ($problem) use (&$plugins) {
                if (stripos($problem['path'], 'plugins') === false) {
                    return;
                }
                $plugin = preg_replace('/(.*)(plugins)(\/)([\w-]+)(.*)/', '$4', $problem['path']);
                $plugins[$plugin][] = [$problem['file'], $problem['line'], $problem['checker'], $problem['category']];
            });

            return collect($plugins);
        }

        return collect();
    }
}

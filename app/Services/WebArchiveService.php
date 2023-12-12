<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class WebArchiveService
{
    const CATEGORIES = [
        'admissions',
        'advancement',
        'alumni',
        'athletics',
        'departments',
        'education',
        'financial-aid',
        'faculty',
        'geography',
        'graduate',
        'gsom',
        'idce',
        'its',
        'leep',
        'luminis',
        'offices',
        'policies',
        'research',
        'resources',
        'school',
        'staff',
        'students',
        'surveys',
        'technology',
    ];

    protected string $server;
    protected string $baseUrl;
    protected string $filePath;

    public function setServer(string $server): self
    {
        $this->server = $server;

        return $this;
    }

    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = $baseUrl;

        return $this;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath =Storage::path('public/' . $filePath);

        return $this;
    }

    public function gather()
    {
        $categories = collect(self::CATEGORIES);

        FileService::toCollection($this->filePath)->each(function ($file) use ($categories) {
            if (stripos($file, 'vti') !== false) {
                return;
            }
            $url = str_replace('./', $this->baseUrl, $file);
            $found = false;
            $categories->each(function ($cat) use ($file, $url, &$found) {
                if (stripos($file, '/' . $cat . '/') !== false) {
                    Storage::append('public/' . $this->server . '/' . $cat . '.txt', $url);
                    $found = true;
                }
            });
            // Dump uncategorized into misc.txt
            if (! $found) {
                Storage::append('public/' . $this->server . '/misc.txt', $url);
            }
        });
    }
}

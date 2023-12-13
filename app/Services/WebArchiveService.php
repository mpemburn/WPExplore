<?php

namespace App\Services;

use App\Facades\Curl;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RedirectMiddleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class WebArchiveService extends WebTestService
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

    public function getFileList(string $filePath): Collection
    {
        $fullPath = Storage::path($filePath);

        return collect(File::allFiles($fullPath));
    }

    public function runTests(): void
    {
        $rootPath = Storage::path('public/' . $this->server);
        collect(File::allFiles($rootPath))->each(function ($file) use ($rootPath) {
            $filename = $file->getFilename();
            $category = $file->getFilenameWithoutExtension();
            $this->setSourceFile($rootPath . '/' . $filename, false);
            $this->testAndWrite($category);
        });
    }

    public function testAndWrite(?string $category = null): void
    {
        if (! file_exists($this->sourceFile)) {
            return;
        }

        $sourceFiles = FileService::toArray($this->sourceFile);
        $sourcePath = $this->baseUrl . $this->filePath;

        collect($sourceFiles)->each(function ($testUrl) use ($category, $sourcePath) {
            echo $testUrl . PHP_EOL;

            $this->createRecord($testUrl, $sourcePath, $category);
        });
    }

}

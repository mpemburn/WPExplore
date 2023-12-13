<?php

namespace App\Services;

use DOMDocument;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RedirectMiddleware;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

abstract class WebTestService
{
    const EXCLUDE_TEXT = ['test', '2', '_prev', '_prv', 'DEBUG', 'ORIG'];

    protected string $server;
    protected string $baseUrl;
    protected string $sourceFile;
    protected string $filePath;
    protected Model $dataModel;

    abstract public function testAndWrite(?string $category = null): void;

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

    public function setSourceFile(string $sourceFile, bool $useStoragePath = true): self
    {
        $this->sourceFile = $useStoragePath
            ? Storage::path($sourceFile)
            : $sourceFile;

        return $this;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function setDataModel(Model $dataModel): self
    {
        $this->dataModel = $dataModel;

        return $this;
    }

    protected function getPageTitle($response): ?string
    {
        if ($response) {
            $rawBody = $response->getBody()->getContents();
            $endHead = stripos($rawBody, '</head>');
            $body = substr($rawBody, 0, $endHead);

            if (! $body || strlen($body) === 0) {
                return null;
            }

            $doc = new DOMDocument();
            libxml_use_internal_errors(true);
            $doc->loadHTML($body);
            libxml_clear_errors();
            $titles = $doc->getElementsByTagName("title");

            return $titles->count() > 0 ? $titles->item(0)->nodeValue : null;
        }

        return null;
    }

    protected function createRecord(string $testUrl, string $sourcePath, ?string $category = null): void
    {
        $indexPath = str_replace($this->baseUrl, '', $testUrl);

        $properties = [];
        $sourcePath = str_replace(['///'], ['/'], $sourcePath . '/' . $testUrl . '/');

        $properties['server'] = $this->server;
        $properties['web_root'] = $testUrl;
        $properties['index_url'] = $indexPath;
        if ($category) {
            $properties['category'] = $category;
        }
        try {
            $client = new Client(['allow_redirects' => ['track_redirects' => true]]);
            $response = $client->get($testUrl);

            $properties['page_title'] = null;
            if ($response) {
                $properties['page_title'] = $this->getPageTitle($response);
            }

            $redirectedUrls = $response->getHeader(RedirectMiddleware::HISTORY_HEADER);
            $redirectPath = end($redirectedUrls);
            $properties['redirect_url'] = $redirectPath;

            if ($redirectPath && $redirectPath !== $sourcePath) {
                echo 'Redirected: ' . $sourcePath . ' -> ' . $redirectPath .  PHP_EOL;
            } else {
                echo 'Not Redirected: ' . $sourcePath . PHP_EOL;
            }

        } catch (GuzzleException $e) {
            echo 'ERROR: ' . $sourcePath . ' -- ' . $e->getCode() . PHP_EOL;
            $properties['error_code'] = $e->getCode();
        }

        $this->dataModel->create($properties);
    }
}

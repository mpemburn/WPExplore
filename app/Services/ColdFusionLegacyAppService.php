<?php

namespace App\Services;
use App\Models\CfLegacyApp;
use DOMDocument;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RedirectMiddleware;
use Illuminate\Support\Facades\Storage;

class ColdFusionLegacyAppService
{
    const EXCLUDE_TEXT = ['test', '2', '_prev', '_prv', 'DEBUG', 'ORIG'];
    public function testAllUrls()
    {
        CfLegacyApp::all()->each(function (CfLegacyApp $app) {
            $result = $this->testUrl($app->index_url);
            if ($result === 'okay') {
                return;
            }
            $changed = $result !== $app->error_code ? '...was ' .$app->error_code : ' (unchanged)';
            echo $app->index_url . ' - ' . $result . $changed . PHP_EOL;
        });
    }

    public function testUrl(string $url): string
    {
        try {

            $client = new Client(['allow_redirects' => ['track_redirects' => true]]);
            $response = $client->get($url);

            return 'okay';

        } catch (GuzzleException $e) {
            return CfLegacyApp::ERROR_CODES[$e->getCode()];
        }
    }

    public function testAndWrite(string $sourceFile, string $baseUrl, string $server, string $path = '')
    {
        $sourceFiles = FileService::toArray(Storage::path($sourceFile));
        $sourcePath = $baseUrl . $path;

        collect($sourceFiles)->each(function ($rawPath) use ($sourcePath, $server) {
            echo $rawPath;
            if (str_replace(self::EXCLUDE_TEXT, '', $rawPath) != $rawPath) {
                return;
            }
            $properties = [];
            $sourcePath = str_replace(['///'], ['/'], $sourcePath . '/' . $rawPath . '/');

            $properties['server'] = $server;
            $properties['web_root'] = $rawPath;
            $properties['index_url'] = $sourcePath;

            try {

                $client = new Client(['allow_redirects' => ['track_redirects' => true]]);
                $response = $client->get($sourcePath);

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
//            !d($properties);
            CfLegacyApp::create($properties);
        });
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

}

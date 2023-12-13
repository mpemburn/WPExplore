<?php

namespace App\Services;
use App\Models\CfLegacyApp;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RedirectMiddleware;

class ColdFusionLegacyAppService extends WebTestService
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

    public function testAndWrite(?string $category = null): void
    {
        if (! file_exists($this->sourceFile)) {
            return;
        }

        $sourceFiles = FileService::toArray($this->sourceFile);

        collect($sourceFiles)->each(function ($rawPath) {
            $testUrl = $this->baseUrl . '/' . $rawPath;
            echo $testUrl . PHP_EOL;

            if (str_replace(self::EXCLUDE_TEXT, '', $rawPath) !== $rawPath) {
                return;
            }

            $this->createRecord($testUrl, $rawPath);

        });
    }
}

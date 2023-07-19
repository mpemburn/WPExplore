<?php

namespace App\Services;
use App\Models\CfLegacyApp;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RedirectMiddleware;

class ColdFusionLegacyAppService
{
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

    protected function testUrl(string $url): string
    {
        try {

            $client = new Client(['allow_redirects' => ['track_redirects' => true]]);
            $response = $client->get($url);

            return 'okay';

        } catch (GuzzleException $e) {
            return CfLegacyApp::ERROR_CODES[$e->getCode()];
        }

    }
}

<?php

namespace App\Helpers;

use App\Models\CfLegacyApp;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class Curl
{
    public function testUrl(string $url): bool
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return ((int) $code) === 200;
    }

    public static function testRedirect(string $url): string
    {
        try {

            $client = new Client(['allow_redirects' => ['track_redirects' => true]]);
            $response = $client->get($url);
            !d($response);
            return 'okay';

        } catch (GuzzleException $e) {
            return CfLegacyApp::ERROR_CODES[$e->getCode()];
        }
    }

    public function getContents(string $url, bool $noFollow = true): string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.example.com/1');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $noFollow);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public function getContentsAsArray(string $url): array
    {
        $contents = static::getContents($url);

        if ($contents) {
            return explode("\n", $contents);
        }

        return [];
    }

}

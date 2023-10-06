<?php

namespace App\Services;

class UrlService
{
    public function testUrl(string $url, ?string $username = null, ?string $password = null): int
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        if ($username && $password) {
            curl_setopt($ch, CURLOPT_USERPWD, env($username) . ":" . env($password));
        }
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return (int)$code;
    }

    public function getContents(string $url): string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.example.com/1');
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

}

<?php

namespace App\Services;

class UrlService
{
    public function testUrl(string $url, ?string $username = null, ?string $password = null): int
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if ($username && $password) {
            curl_setopt($ch, CURLOPT_USERPWD, env($username) . ":" . env($password));
        }
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return (int) $code;
    }

}

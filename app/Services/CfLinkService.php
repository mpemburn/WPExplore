<?php

namespace App\Services;

use App\Models\CfLinks;

class CfLinkService
{
    public function scanFacultyBios()
    {
        $service = new BrowserService();
        $bios = CfLinks::whereNull('redirect')
            ->where('url', 'LIKE', '%facultybio%')
            ->get();
        $bios->each(function ($bio) use ($service) {
            $url = $bio->url;
            $name = $service->scrapeElement($url, '#biotop > div > h1');
            $email = $service->scrapeElement($url, '[href^="mailto:"]');

            echo '"' . $url . '","' . $name . '","' . $email . '"<br>';
        });
    }

    public function scanDepartments()
    {

    }
}

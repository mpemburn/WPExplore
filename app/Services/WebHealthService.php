<?php

namespace App\Services;

use App\Facades\Curl;

class WebHealthService
{
    public function checkHealth()
    {
        $url = request('url');

        $response = Curl::getContents('https://clark:clarkadmin@www.testing.clarku.edu');

        flash('in the pants');

        return response()->json(['response' => $response]);
    }
}

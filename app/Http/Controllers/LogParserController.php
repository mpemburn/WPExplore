<?php

namespace App\Http\Controllers;

use App\Services\LogParserService;
use Illuminate\Http\JsonResponse;

class LogParserController extends Controller
{
    public function show(LogParserService $service): JsonResponse
    {
        return response()->json([
            'success' => true,
        ]);
    }

    public function store(LogParserService $service): JsonResponse
    {
        $done = $service->toggleDone(request('logId'), request('lineNum'));

        return response()->json([
            'success' => true,
            'lineNum' => request('lineNum'),
            'done' => $done
        ]);
    }
}

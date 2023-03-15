<?php

namespace App\Http\Controllers;

use App\Services\LogParserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogParserController extends Controller
{
    public function show(LogParserService $service): JsonResponse
    {
        return response()->json([
            'success' => true,
        ]);
    }

    public function store(LogParserService $service, Request $request): JsonResponse
    {
        $done = $service->toggleDone($request);

        return response()->json([
            'success' => true,
            'lineNum' => request('lineNum'),
            'done' => $done
        ]);
    }
}

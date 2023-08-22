<?php

namespace App\Http\Controllers;

use App\Services\BlogService;
use App\Services\DatabaseService;
use Illuminate\Http\Request;

class MigrationController extends Controller
{
    public function index()
    {
        $databases = DatabaseService::getDatabaseList();

        return view('migrate', [
            'databases' => $databases,
        ]);
    }

    public function getSubsites(BlogService $service, Request $request)
    {
        $database = request('database');
        if (! $database) {
            return response()->json(['error' => 'No Database']);
        }
        DatabaseService::setDb($database);

        $subsites = $service->getActiveBlogs()->toArray();

        return response()->json(['subsites' => $subsites]);
    }
}

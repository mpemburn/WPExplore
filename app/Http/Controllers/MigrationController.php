<?php

namespace App\Http\Controllers;

use App\Services\BlogService;
use App\Services\DatabaseService;
use App\Services\MigrateTablesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

    public function migration(MigrateTablesService $service, Request $request)
    {
        $databaseFrom = request('databaseFrom');
        $databaseTo = request('databaseTo');
        $fromValues = request('from');
        $blogIds = explode(',', $fromValues);

        $results = $service->migrateMultiple($databaseFrom, $databaseTo, $blogIds);

        return response()->json(['results' => $results]);
    }
}

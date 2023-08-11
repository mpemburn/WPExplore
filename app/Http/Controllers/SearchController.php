<?php

namespace App\Http\Controllers;
use App\Factories\SearcherFactory;
use App\Services\DatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function search()
    {
        $databases = DatabaseService::getDatabaseList();

        return view('search', ['databases' => $databases]);
    }

    public function index(Request $request)
    {
        $database = request('database');
        if (! $database) {
            return response()->json(['error' => 'No Database']);;
        }
        DatabaseService::setDb($database);
        $searchType = request('type');
        $searchText = request('text');
        if ($searchType && $searchText) {
            $searcher = SearcherFactory::build($searchType);
            if (! $searcher) {
                return response()->json(['error' => 'No Search']);;
            }
            $html = $searcher->run($searchText)->render();
            $count = $searcher->getCount();

            return response()->json(['html' => $html, 'found' => $count]);
        }

        return response()->json(['error' => 'Nothing']);
    }
}

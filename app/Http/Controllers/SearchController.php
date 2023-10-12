<?php

namespace App\Http\Controllers;
use App\Factories\SearcherFactory;
use App\Services\DatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $source = request('source');
        $useList = $source === 'test' ? 'INSTALLED_TEST_DATABASES' : 'INSTALLED_DATABASES';

        $databases = DatabaseService::getDatabaseList($useList);

        return view('search', ['databases' => $databases]);
    }

    public function search(Request $request)
    {
        $database = request('database');
        if (! $database) {
            return response()->json(['error' => 'No Database']);;
        }
        DatabaseService::setDb($database);
        $searchType = request('type');
        $searchText = request('text');
        $exact = (bool)request('exact');
        if ($searchType && $searchText) {
            $searcher = SearcherFactory::build($searchType);
            if (! $searcher) {
                return response()->json(['error' => 'No Search']);;
            }
            $html = $searcher->run($searchText, $exact)->render();
            $count = $searcher->getCount();

            return response()->json(['html' => $html, 'found' => $count]);
        }

        return response()->json(['error' => 'Nothing']);
    }
}

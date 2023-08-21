<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Services\CsvService;
use App\Services\DatabaseService;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class CsvController extends Controller
{
    public function index()
    {
        $databases = DatabaseService::getDatabaseList();

        return view('csv', [
            'csvTypes' => CsvService::AVAILABLE_CSV_TYPES,
            'databases' => $databases,
            'todayDate' => Carbon::now()->format('Y-m-d')
        ]);
    }

    public function getMinDate(Request $request)
    {
        $database = request('database');
        if (! $database) {
            return response()->json(['error' => 'No Database']);
        }
        DatabaseService::setDb($database);
        $last = Blog::min('last_updated');
        $minDate = '';
        if ($last) {
            $minDate = Carbon::parse($last)->format('Y-m-d');
        }

        return response()->json(['minDate' => $minDate]);
    }

    public function download(CsvService $service, Request $request)
    {
        $database = request('database');
        $type = request('csv_type');
        $filenameDefault = request('filename_default');
        $filenameOverride = request('filename_override');
        $filename = $filenameOverride ?: $filenameDefault;

        if (! str_ends_with($filename, '.csv')) {
            $filename .= '.csv';
        }

        $data = $service->callCsvMethod($database, $type, $filename);

        return response()->json([
            'success' => true,
            'data' => $data,
            'filename' => $filename
        ]);
    }
}

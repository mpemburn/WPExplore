<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class Csv
{
    public function toCollection(string $file): ?Collection
    {
        if (! file_exists($file)) {
            return null;
        }

        $csv = file_get_contents($file);
        // 1. Split by new line. Use the PHP_EOL constant for cross-platform compatibility.
        $lines = explode(PHP_EOL, $csv);

        // 2. Extract the header and convert it into a Laravel collection.
        $header = collect(str_getcsv(array_shift($lines)));
        $headerCount = count($header);

        // 3. Convert the rows into a Laravel collection.
        $rows = collect($lines);

        // 4. Map through the rows and combine them with the header to produce the final collection.
        return $rows->map(function($row) use ($header, $headerCount) {
            $rowArray = str_getcsv($row);
            $count = count($rowArray);
            if ($count < $headerCount) {
                $rowArray = array_merge($rowArray, array_fill(0, $headerCount - $count, null));
            }

            try {
                return $header->combine($rowArray);
            } catch (\Exception $e) {
                return null;
            }
        });
    }
}

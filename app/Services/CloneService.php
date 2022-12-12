<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class CloneService
{
    public function clone(string $sourceDb, string $destDb, string $prefix): void
    {
        $tables = $this->getTables($sourceDb, $prefix);

        if ($tables->count() > 0) {
            $tables->each(function ($table) use ($sourceDb, $destDb) {
                $success = DB::statement("CREATE TABLE {$destDb}.{$table} LIKE {$sourceDb}.{$table};");

                if ($success) {
                    $this->insertData($sourceDb, $destDb, $table);
                }
            });
        }
    }

    public function getTables(string $dbName, string $prefix = '%'): Collection
    {
        $tables = collect();

        // Escape underscores
        $prefix = str_replace('_', '\_', $prefix);
        collect(DB::select("SHOW TABLES FROM {$dbName} LIKE '{$prefix}%';"))
            ->each(function ($result) use ($dbName, $prefix, &$tables) {
                $tables->push($result->{"Tables_in_{$dbName} ($prefix%)"});
            });

        return $tables;
    }

    protected function insertData(string $sourceDb, string $destDb, string $table): void
    {
        $tableData = DB::select(DB::raw("SELECT * FROM {$sourceDb}.{$table};"));

        collect($tableData)->each(function ($row) use ($destDb, $table) {
            $rowData = get_object_vars($row);
            // Create a comma-separated string of the columns
            $columns = implode(',', array_keys($rowData));
            $values = array_values($rowData);
            // Create a comma-separated string of ?'s
            $prep = implode(',', array_fill(0, count($values), '?'));

            $query = "INSERT INTO {$destDb}.{$table} ({$columns}) VALUES ({$prep})";

            DB::insert($query, $values);
        });
    }
}

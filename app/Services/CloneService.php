<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Config;
use PDOException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class CloneService
{
    public function clone(string $sourceDb, string $destDb, string $prefix, ?int $blogId = null): void
    {
        $tables = $this->getTables($sourceDb, $prefix);
        $created = collect();

        if ($tables->count() > 0) {
            // Temporarily set strict mode to false in order to avoid missing default value errors on create.
            DB::statement('SET sql_mode = false;');

            $tables->each(function ($table) use ($sourceDb, $destDb, &$created) {
                try {
                    $success = DB::statement("CREATE TABLE {$destDb}.{$table} LIKE {$sourceDb}.{$table};");
                } catch (Exception $e) {
                    // Remove the tables we've created if there's an exception.
                    $this->removeTables($destDb, $created);

                    throw new PDOException('Unable to create "' . $table . '"' . PHP_EOL . 'Reason: ' . $e->getMessage());
                }

                if ($success) {
                    $this->insertData($sourceDb, $destDb, $table);
                    $created->push($table);
                }
            });

            if ($blogId) {
                $this->addSiteToBlogs($sourceDb, $destDb, $blogId);
            }
        }
    }

    public function getTables(string $dbName, string $prefix = '%'): Collection
    {
        $tables = collect();

        // Escape underscores
        $prefix = str_replace('_', '\_', $prefix);
        collect(DB::select("SHOW TABLES FROM {$dbName} LIKE '{$prefix}%';"))
            ->each(function ($result) use (&$tables) {
                // Convert the stdClass to an array, and get the first element
                $table = current((array)$result);
                $tables->push($table);
            });

        return $tables;
    }

    protected function insertData(string $sourceDb, string $destDb, string $table): void
    {
        $sql = "SELECT * FROM {$sourceDb}.{$table};";

        $this->doInsert($sql, $destDb, $table);
    }

    protected function doInsert(string $sqlSelect, string $destDb, string $table): void
    {
        $tableData = DB::select(DB::raw($sqlSelect));

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

    protected function addSiteToBlogs(string $sourceDb, string $destDb, int $blogId): void
    {
        $findBlogs = DB::select("SHOW TABLES FROM {$destDb} LIKE '%\_blogs';");
        if ($findBlogs) {
            $blogsTable = current((array)$findBlogs[0]);
            $this->doInsert(
                "SELECT * FROM {$sourceDb}.{$blogsTable} WHERE blog_id = {$blogId}",
                $destDb,
                $blogsTable
            );
        }
    }

    protected function removeTables(string $destDb, Collection $tablesCreated): void
    {
        $tablesCreated->each(function ($table) use ($destDb) {
           DB::statement("DROP TABLE {$destDb}.{$table}");
        });
    }
}

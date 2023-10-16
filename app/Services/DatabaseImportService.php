<?php

namespace App\Services;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DatabaseImportService
{
    protected Collection $data;
    protected Collection $createStatements;
    protected Collection $insertStatements;
    protected Collection $createLines;
    protected Collection $insertLines;
    protected  bool $loadingCreate = false;
    protected  bool $loadingInsert = false;
    protected string $previousLine;

    public function __construct()
    {
        $this->data = collect();
        $this->createStatements = collect();
        $this->insertStatements = collect();
    }

    public function loadData(string $filePath): self
    {
        $this->data = (new FileService())->getContentsCollection($filePath);

        return $this;
    }

    public function setDatabase(string $dbName): self
    {
        Database::setDb($dbName);

        return $this;
    }

    public function process(): self
    {
        $this->data->each(function ($line) {
            // Discard commented lines
            if (
                str_starts_with($line, '--')
                || str_starts_with($line, 'LOCK')
                || str_starts_with($line, 'UNLOCK')
            ) {
                return;
            }

            $this->loadCreateStatements($line);
            $this->loadInsertStatements($line);
            $this->previousLine = $line;
        });

        return $this;
    }

    public function import(): void
    {
        $this->createStatements->each(function ($statement) {
//            !d($statement);
            DB::statement($statement);
        });
        $this->insertStatements->each(function ($statement) {
            !d($statement);
            try {
                DB::statement($statement);
            } catch (\Exception $e) {
                echo $e->getMessage() . PHP_EOL;
            }
        });
    }

    protected function loadCreateStatements($line): void
    {
        if ($this->loadingCreate) {
            if (str_ends_with($line, ';')) {
                $this->loadingCreate = false;
                $this->createLines->push($line);
                $statement = $this->correctCreate($this->createLines->implode("\n"));
                $this->createStatements->push($statement);
            }
            $this->createLines->push($line);
        }
        if (str_starts_with($line, 'CREATE TABLE')) {
            $this->loadingCreate = true;
            $this->createLines = collect();
            $this->createLines->push($line);
        }
    }

    protected function loadInsertStatements($line): void
    {
        if ($this->loadingInsert) {
            if (str_ends_with($line, ';')) {
                $this->loadingInsert = false;
                $this->insertLines->push($line);
                $statement = $this->correctInsert($this->insertLines->implode("\n"));
                $this->insertStatements->push($statement);
            }
            $this->insertLines->push($line);
        }
        if (str_starts_with($line, 'INSERT')) {
            $this->loadingInsert = true;
            $this->insertLines = collect();
            // Prepend with the "LOCK TABLES" statement
//            $this->insertLines->push($this->previousLine);
            $this->insertLines->push($line);
        }
    }

    protected function correctCreate(string $statement): string
    {
        return str_replace(
            [
                'CREATE TABLE',
                "'0000-00-00 00:00:00'",
            ],
            [
                'CREATE TABLE IF NOT EXISTS',
                'CURRENT_TIMESTAMP',
            ],
            $statement
        );
    }

    protected function correctInsert(string $statement): string
    {
        return str_replace(
            [
                'INSERT INTO',
            ],
            [
                'REPLACE INTO',
            ],
            $statement
        );
    }

}

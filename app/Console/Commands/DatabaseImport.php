<?php

namespace App\Console\Commands;

use App\Services\DatabaseImportService;
use App\Services\DatabaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:import {--source=} {--db=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a database from a .sql file.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $source = $this->option('source');
        $db = $this->option('db');

        $sql = Storage::path($source);

        echo Carbon::now()->format('m/d/Y H:i:s') . PHP_EOL;

        ini_set('memory_limit', '-1');
        (new DatabaseImportService())
            ->loadData($sql)
            ->setDatabase($db)
            ->process()
            ->import();

        echo Carbon::now()->format('m/d/Y H:i:s') . PHP_EOL;

        return Command::SUCCESS;
    }
}

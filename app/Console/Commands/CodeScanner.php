<?php

namespace App\Console\Commands;

use App\Models\CodeScan;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CodeScanner extends Command
{
    protected $signature = 'dev:codescan {--root=} {--search=}';
    protected $description = 'Scan files for search string.';

    protected string $filename;
    protected Collection $errorDump;

    public function handle()
    {
        $this->errorDump = collect();

        $rootPath = $this->option('root');

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($rootPath));

        collect($iterator)->each(function ($file) {
            if (!str_ends_with($file, '.php')) {
                return;
            }
            $this->filename = $file;

            echo 'Reading: ' . $file . PHP_EOL;

            $this->readFile($file);
        });
    }

    protected function readFile(string $filePath)
    {
        $lineNum = 1;
        $contents = file_get_contents($filePath);

        collect(explode("\n", $contents))->each(function ($line) use (&$lineNum,) {
            $result = $this-> getMatches($line, $lineNum);

            $lineNum++;
        });
    }

    protected function getMatches(string $line, int $lineNum): bool
    {
        $matches = null;

        $search = $this->option('search');
        if (stripos($line, $search) !== false) {
            CodeScan::create([
                'key_word' => $search,
                'filename' => $this->filename,
                'line_num' => $lineNum,
                'line_contents' => trim($line),
            ]);

            return true;
        }

        return false;
    }

}

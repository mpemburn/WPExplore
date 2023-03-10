<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class LogParserService
{
    protected string $logPrefix;
    protected Collection $log;
    protected Collection $done;
    protected Collection $unique;
    protected Collection $includes;
    protected Collection $filters;

    public function __construct(string $logPrefix, array $includes = [], array $filters = [])
    {
        $this->logPrefix = $logPrefix;
        $this->includes = collect($includes);
        $this->filters = collect($filters);
        $this->filters->prepend('PHP Notice');

        $this->readLog()
            ->readLinesDone()
            ->parse()
            ->display();
    }

    protected function parse(): self
    {
        $this->unique = collect();
        $lineNum = 0;

        $this->log->each(function ($line) use (&$lineNum) {
            $lineNum++;
            if ($this->shouldExclude($line)) {
                return;
            }
            if ($this->shouldInclude($line)) {
                $line = preg_replace('/\[(.*)\] /', '', $line);
                if ($this->unique->doesntContain(function ($value) use ($line) {
                    return $value['text'] === $line;
                })) {
                    $thisLine = ['line_num' => $lineNum, 'text' => $line];
                    $this->unique->push($thisLine);
                }
             }
        });


        return $this;
    }

    protected function display()
    {
        $this->echoStyles();

        $sorted = $this->unique->sortBy('line-num');
        $sorted->each(function ($line) {
            if ($this->done->contains($line['line_num'])) {
                echo '<div class="line-num done">';
            } else {
                echo '<div class="line-num">';
            }
            echo $line['line_num'] . '</div>';
            echo $line['text'] . '<br>';
        });
    }

    protected function readLog(): self
    {
        $logFile = Storage::path($this->logPrefix . '_error.log');
        if (file_exists($logFile)) {
            $contents = file_get_contents($logFile);
            $this->log = collect(explode("\n", $contents));
        }

        return $this;
    }

    protected function readLinesDone(): self
    {
        $this->done = collect();
        $excludeFile = Storage::path($this->logPrefix . '_done.txt');
        if (file_exists($excludeFile)) {
            $contents = file_get_contents($excludeFile);
            $this->done = collect(explode("\n", $contents));
        }

        return $this;
    }

    protected function shouldInclude(string $line): bool
    {
        if ($this->includes->isEmpty()) {
            return true;
        }

        $truth = false;
        $this->includes->each(function ($condition) use ($line, &$truth) {
            if ($truth) {
                return;
            }

            $truth = stripos($line, $condition) !== false;
        });

        return $truth;
    }

    protected function shouldExclude(string $line): bool
    {
        if ($this->filters->isEmpty()) {
            return false;
        }

        $truth = false;
        $this->filters->each(function ($condition) use ($line, &$truth) {
                if ($truth) {
                    return;
                }

                $truth = stripos($line, $condition) !== false;
            });

        return $truth;
    }

    protected function echoStyles()
    {
        echo '<style>';
        echo '.line-num {';
        echo '    display: inline-block;';
        echo '    min-width: 50px;';
        echo '    padding: 2px;';
        echo '    margin-right: 5px;';
        echo '    margin-bottom: 2px;';
        echo '    border: 1px solid blue;';
        echo '    background-color: lightblue;';
        echo '    text-align: right;';
        echo '    font-size: 8pt;';
        echo '    font-size: 10pt;';
        echo '    font-family: sans-serif;';
        echo '}';
        echo '.done {';
        echo '    background-color: lightgreen;';
        echo '}';
        echo '</style>';
    }

}

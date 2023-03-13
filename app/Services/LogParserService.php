<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class LogParserService
{
    protected string $logPrefix;
    protected ?string $logFilePath = null;
    protected ?string $doneFilePath = null;
    protected Collection $log;
    protected Collection $done;
    protected Collection $unique;
    protected Collection $includes;
    protected Collection $filters;

    public function run(string $logPrefix, array $includes = [], array $filters = [])
    {
        $this->logPrefix = $logPrefix;
        $this->includes = collect($includes);
        $this->log = collect();
        $this->filters = collect($filters);
        $this->filters->prepend('PHP Notice');

        return $this->readLog()
            ->readLinesDone()
            ->parse();
    }

    public function display(): array
    {
        $result = collect();
        $sorted = $this->unique->sortBy('line-num');
        $sorted->each(function ($line) use ($result) {
            $html = '';
            if ($this->done->contains($line['line_num'])) {
                $html .= '<div class="line-num done" data-line-num="' . $line['line_num'] . '">';
            } else {
                $html .= '<div class="line-num" data-line-num="' . $line['line_num'] . '">';
            }
            $html .= $line['line_num'] . '</div>';
            $html .= $this->makeLink($line['line_num'], $line['text']) . '<br>';

            $result->push($html);
        });

        return $result->toArray();
    }

    public function getLogPrefix(): string
    {
        return $this->logPrefix;
    }

    public function toggleDone(string $logPrefix, string $lineNum)
    {
        $this->logPrefix = $logPrefix;
        $this->readLinesDone();

        if ($this->done->contains($lineNum)) {
            return $this->removeFromDone($lineNum);
        } else {
            return $this->addToDone($lineNum);
        }
    }
    protected function addToDone(string $lineNum): bool
    {
        $contents = file_get_contents($this->doneFilePath);
        $contents .= $lineNum . "\n";

        return file_put_contents($this->doneFilePath, $contents) !== false;
    }

    protected function removeFromDone(string $lineNum): bool
    {
        $contents = file_get_contents($this->doneFilePath);
        $revised = collect(explode("\n", $contents))->map(function ($num) use ($lineNum) {
            return (int)$num === (int)$lineNum ? null : $num;
        })->filter()
        ->implode("\n");

        return file_put_contents($this->doneFilePath, $revised) !== false;
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

    protected function makeLink(string $lineNum, string $line): string
    {
        $result = $line;
        if (preg_match('/(\/dom28151\/)(wp-content)(.*)(\.php)/', $line, $matches)) {
            $matched = $matches[0];
            $path = str_replace('/dom28151', '', $matched);
            $link = '<span style="text-decoration: underline; color: blue; cursor: pointer"';
            $link .= ' data-line-num="' . $lineNum . '"';
            $link .= ' data-link="' . $path . '">' . $matched . '</span>';
            $result = str_replace($matched, $link, $line);
        }

        return $result;
    }

    protected function readLog(): self
    {
        if (! $this->logFilePath) {
            $path = Storage::path($this->logPrefix . '_error.log');
            $this->logFilePath = file_exists($path) ? $path : null;
        }

        if ($this->logFilePath) {
            $contents = file_get_contents($this->logFilePath);
            $this->log = collect(explode("\n", $contents));
        }

        return $this;
    }

    protected function readLinesDone(): self
    {
        $this->done = collect();
        if (! $this->doneFilePath) {
            $path = Storage::path($this->logPrefix . '_done.txt');
            $this->doneFilePath = file_exists($path) ? $path : null;
        }

        if ($this->doneFilePath) {
            $contents = file_get_contents($this->doneFilePath);
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

}

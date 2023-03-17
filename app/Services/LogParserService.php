<?php

namespace App\Services;

use App\Models\LogParserCompleted;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class LogParserService
{
    protected string $logPrefix;
    protected ?string $logFilePath = null;
    protected ?string $doneFilePath = null;
    protected string $appPath;
    protected Collection $log;
    protected Collection $done;
    protected Collection $unique;
    protected Collection $includes;
    protected Collection $filters;
    protected string $codePath;

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
            if ($this->doneExists($line['line_num'])) {
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

    public function toggleDone(Request $request)
    {
        $this->logPrefix = $request->get('logPrefix');
        $this->readLinesDone();

        if ($this->doneExists($request->get('lineNum'), $request->get('logPrefix'))) {
            return !$this->removeFromDone($request);
        } else {
            return $this->addToDone($request);
        }
    }

    public function setCodePath(string $codePath): self
    {
        $this->codePath = $codePath;

        return $this;
    }

    public function getCodePath(): string
    {
        return addslashes($this->codePath);
    }

    public function setAppPath(string $appPath): self
    {
        $this->appPath = $appPath;

        return $this;
    }

    public function getAppPath(): string
    {
        return addslashes($this->appPath);
    }

    protected function doneExists(string $lineNum, ?string $logPrefix = null): bool
    {
        $logPrefix = $logPrefix ?: $this->logPrefix;

        return LogParserCompleted::where('log_id', $logPrefix)
            ->where('line_number', $lineNum)
            ->exists();
    }

    protected function addToDone(Request $request): bool
    {
        LogParserCompleted::create([
            'log_id' => $request->get('logPrefix'),
            'line_number' => $request->get('lineNum'),
            'line_data' => $request->get('lineText'),
        ]);

        return true;
    }

    protected function removeFromDone(Request $request): bool
    {
        LogParserCompleted::where('log_id', $request->get('logPrefix'))
            ->where('line_number', $request->get('lineNum'))
            ->delete();

        return false;
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
            preg_match('/(.php on line )([\d]+)/', $line, $lineMatches);

            $matched = $matches[0];
            $fileLine = $lineMatches[2] ?? null;
            $path = str_replace('/dom28151', '', $matched);
            $link = '<span class="link"';
            $link .= ' data-link="' . $path . '" data-file-line="' . $fileLine . '">' . $path . '</span>';
            $result = str_replace($matched, $link, $line);
            $result .= '<div class="log-line-hidden" data-line-num="' . $lineNum . '">' . $line . '</div>';
        }

        return '<div class="log-line">' . $result . '</div>';
    }

    protected function readLog(): self
    {
        if (!$this->logFilePath) {
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
        if (!$this->doneFilePath) {
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

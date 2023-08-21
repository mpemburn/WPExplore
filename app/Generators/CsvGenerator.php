<?php

namespace App\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

abstract class CsvGenerator
{
    protected Collection $data;

    public function __construct(protected string $filename)
    {
    }

    abstract public function getColumns(): ?array;

    public function setData(Collection $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getData(): Collection
    {
        return $this->data;
    }

    public function toString(): string
    {
        $dataString = implode(',', $this->getColumns()) . PHP_EOL;

        $this->data->each(function ($row) use (&$dataString) {
            $dataString .= implode(',', $row) . PHP_EOL;
        });

        return $dataString;
    }

    public function unsetColumns(array $columns): self
    {
        if ($this->data->isNotEmpty()) {
            $this->data = $this->data->transform(function ($item) use ($columns) {
                foreach ($columns as $column) {
                    unset($item[$column]);
                }

                return $item;
            });
        }
        return $this;
    }

    public function run(): ?StreamedResponse
    {
        if (! $this->data && ! $this->filename) {
            return null;
        }

        $callback = function () {
            $columns = $this->getColumns();
            $rows = $this->getData();

            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $rows->each(function ($row) use ($file) {
                fputcsv($file, $row);
            });

            fclose($file);
        };

        return Response::stream($callback, 200, $this->getHeaders($this->filename));
    }

    protected function getHeaders(string $filename): array
    {
        return [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $filename,
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];
    }
}

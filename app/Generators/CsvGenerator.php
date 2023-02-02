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

        return Response::stream($this->getCallback($this->data), 200, $this->getHeaders($this->filename));
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

    protected function getCallback(Collection $rows): callable
    {
        $columns = $this->getColumns();
        return function () use ($rows, $columns)
        {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $rows->each(function ($row) use ($file) {
                fputcsv($file, $row);
            });

            fclose($file);
        };
    }
}

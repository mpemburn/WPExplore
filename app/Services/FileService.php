<?php

namespace App\Services;
use Illuminate\Support\Collection;

class FileService
{
    static function toArray(string $filename): ?array
    {
        if (file_exists($filename)) {
            $contents = file_get_contents($filename);

            return explode("\n", $contents);
        }

        return null;
    }

    static function toCollection(string $filename): ?Collection
    {
        $contents = self::toArray($filename);
        if ($contents) {
            return collect($contents);
        }

        return null;
    }

    static function toMap(string $filename, array $headers): ?Collection
    {
        $contents = collect(array_map('str_getcsv', file($filename)));
        if ($contents) {
            return $contents->map(function ($row) use ($headers) {
                $mapping = [];
                foreach ($row as $index => $field) {
                    $mapping[$headers[$index]] = $row[$index];
                }
                return $mapping;

            });
        }

        return null;
    }
    public function getFileContents(string $filepath): ?string
    {
        if (file_exists($filepath)) {
            return file_get_contents($filepath);
        }

        return null;
    }

    public function getContentsArray(string $filepath): array
    {
        $contents = $this->getFileContents($filepath);
        if ($contents) {
            return explode("\n", $contents);
        }

        return [];
    }

    public function getContentsCollection(string $filepath): Collection
    {
        return collect($this->getContentsArray($filepath));
    }
}

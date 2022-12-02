<?php

namespace App\Generators;

class BlogsCsvGenerator extends CsvGenerator
{
    public function getColumns(): ?array
    {
        return  [
            'Blog ID',
            'Last Update',
            'Admin Email',
            'URL',
        ];
    }
}

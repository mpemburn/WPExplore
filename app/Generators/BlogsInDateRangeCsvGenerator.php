<?php

namespace App\Generators;

class BlogsInDateRangeCsvGenerator extends CsvGenerator
{
    public function getColumns(): ?array
    {
        return  [
            'Blog ID',
            'Last Update',
            'URL',
            'Admin Email',
            'Theme',
            'Active Plugins',
        ];
    }
}

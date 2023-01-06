<?php

namespace App\Generators;

class PluginsCsvGenerator extends CsvGenerator
{
    public function getColumns(): ?array
    {
        return  [
            'Name',
            'Folder',
            'Sites',
        ];
    }
}

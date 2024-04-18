<?php

namespace App\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ?Collection toCollection(string $file);
 */
class Csv extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'csv';
    }
}

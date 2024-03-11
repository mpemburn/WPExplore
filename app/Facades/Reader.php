<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static ?string contents(string $filenname);
 * @method static ?array getContentsAsArray(string $filenname);
 */
class Reader extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'reader';
    }

}

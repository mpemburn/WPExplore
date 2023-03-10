<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

// Run with vendor/bin/rector process
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        'C:\Users\mpemburn\Documents\Sandbox\wpexplore\storage\app\all_themes\www\clarku-welcome-parent'
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

//     define sets of rules
        $rectorConfig->sets([
            LevelSetList::UP_TO_PHP_80
        ]);
};

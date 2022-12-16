<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        //__DIR__ . '/src/nextgen-gallery-sidebar-widget',
        'C:\Users\mpemburn\Documents\Dev\wordpress.clarku.edu\wp-content\plugins\google-analytics-async',
//        'C:\Users\mpemburn\Documents\Dev\wordpress.clarku.edu\wp-content\themes\zerif-lite'
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

//     define sets of rules
        $rectorConfig->sets([
            LevelSetList::UP_TO_PHP_80
        ]);
};

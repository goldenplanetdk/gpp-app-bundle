<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/Command',
        __DIR__ . '/Controller',
        __DIR__ . '/DependencyInjection',
        __DIR__ . '/EventSubscriber',
        __DIR__ . '/Security',
    ]);

    $rectorConfig->sets([
        SymfonySetList::SYMFONY_54,
        SymfonySetList::SYMFONY_CODE_QUALITY,
        SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION,
    ]);

//    $rectorConfig->sets([LevelSetList::UP_TO_PHP_74]);
};

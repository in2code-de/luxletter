<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'extension-lux' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:luxletter/Resources/Public/Icons/lux.svg',
    ],
    'extension-luxletter' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:luxletter/Resources/Public/Icons/Extension.svg',
    ],
    'extension-lux-module' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:luxletter/Resources/Public/Icons/lux_white.svg',
    ],
    'extension-luxletter-module' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:luxletter/Resources/Public/Icons/lux_module_newsletter.svg',
    ],
    'extension-luxletter-star' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:luxletter/Resources/Public/Icons/star.svg',
    ],
    'teaser' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:luxletter/Resources/Public/Icons/ctype-teaser.svg',
    ],
    'luxletter-widget-receiver' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:luxletter/Resources/Public/Icons/widget_receiver.svg',
    ],
    'luxletter-widget-newsletter' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:luxletter/Resources/Public/Icons/widget_newsletter.svg',
    ],
    'apps-pagetree-luxletter' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:luxletter/Resources/Public/Icons/luxletter_doktype.svg',
    ],
    'apps-pagetree-luxletter-contentFromPid' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:luxletter/Resources/Public/Icons/luxletter_doktype.svg',
    ],
];

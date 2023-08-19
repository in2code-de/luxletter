<?php

return [
    'lux_module' => [
        'labels' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_mod.xlf',
        'iconIdentifier' => 'extension-lux-module',
    ],
    'lux_Luxletter' => [
        'parent' => 'lux_module',
        'position' => [],
        'access' => 'user',
        'iconIdentifier' => 'extension-luxletter-module',
        'path' => '/module/lux/Luxletter',
        'labels' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_mod_newsletter.xlf',
        'extensionName' => 'Luxletter',
        'controllerActions' => [
            \In2code\Luxletter\Controller\NewsletterController::class => [
                'dashboard',
                'list',
                'resetFilter',
                'edit',
                'update',
                'new',
                'create',
                'enable',
                'disable',
                'delete',
                'receiver',
            ],
        ],
    ],
];

<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'luxletter - TYPO3 Email Marketing Newsletter Tool',
    'description' => 'Free newsletter extension for TYPO3 for an individual email marketing. A lot of analytics and modern concepts. Works with and without EXT:lux.',
    'category' => 'plugin',
    'version' => '18.0.0',
    'author' => 'Alex Kellner',
    'author_email' => 'alexander.kellner@in2code.de',
    'author_company' => 'in2code.de',
    'state' => 'stable',
    'constraints' => [
        'depends' => [
            'typo3' => '10.5.0-12.4.99'
        ],
        'conflicts' => [],
        'suggests' => [
            'lux' => '0.0.0-0.0.0',
            'dashboard' => '0.0.0-0.0.0'
        ]
    ]
];

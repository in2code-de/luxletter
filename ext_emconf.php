<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "lux".
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'luxletter - TYPO3 Email Marketing Newsletter Tool',
    'description' => 'Free newsletter extension for TYPO3 for an individual email marketing. A lot of analytics and modern concepts. Works with and without EXT:lux.',
    'category' => 'plugin',
    'version' => '1.2.2',
    'author' => 'Alex Kellner',
    'author_email' => 'alexander.kellner@in2code.de',
    'author_company' => 'in2code.de',
    'state' => 'stable',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-9.5.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    '_md5_values_when_last_written' => '',
];

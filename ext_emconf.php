<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "lux".
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'lux - TYPO3 Marketing Automation',
    'description' => 'Living User Experience - LUX - the Marketing Automation tool for TYPO3. Enterprise Edition.
        Enterprise edition as addon to the Community edition.',
    'category' => 'plugin',
    'version' => '2.1.0',
    'author' => 'Alex Kellner',
    'author_email' => 'alexander.kellner@in2code.de',
    'author_company' => 'in2code.de',
    'state' => 'stable',
    'constraints' => [
        'depends' => [
            'lux' => '4.0.0-4.99.99'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    '_md5_values_when_last_written' => '',
];

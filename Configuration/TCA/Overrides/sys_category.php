<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$columns = [
    'luxletter_newsletter_category' => [
        'exclude' => true,
        'label' =>
            'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:sys_category.luxletter_newsletter_category',
        'config' => [
            'type' => 'check',
        ],
    ],
];
ExtensionManagementUtility::addTCAcolumns('sys_category', $columns);
ExtensionManagementUtility::addToAllTCAtypes('sys_category', 'luxletter_newsletter_category', '', 'after:parent');

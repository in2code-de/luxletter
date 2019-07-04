<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$columns = [
    'crdate' => [
        'exclude' => false,
        'config' => [
            'type' => 'none'
        ]
    ]
];
ExtensionManagementUtility::addTCAcolumns('fe_users', $columns);
ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'crdate');

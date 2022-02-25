<?php

use In2code\Luxletter\Domain\Repository\LanguageRepository;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(
    function () {
        $columns = [
            'crdate' => [
                'exclude' => false,
                'config' => [
                    'type' => 'none'
                ]
            ],
            'luxletter_language' => [
                'exclude' => true,
                'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:fe_groups.luxletter_language',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        ['Standard', 0],
                    ],
                    'foreign_table' => LanguageRepository::TABLE_NAME,
                    'default' => 0,
                ]
            ]
        ];
        ExtensionManagementUtility::addTCAcolumns('fe_users', $columns);
        ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'luxletter_language', '', 'after:image');
    }
);

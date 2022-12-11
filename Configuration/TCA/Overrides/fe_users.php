<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(
    function () {
        $columns = [
            'crdate' => [
                'exclude' => false,
                'config' => [
                    'type' => 'none',
                ],
            ],
            'luxletter_language' => [
                'exclude' => true,
                'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:fe_groups.luxletter_language',
                'config' => [
                    'type' => 'language',
                ],
            ],
        ];
        ExtensionManagementUtility::addTCAcolumns('fe_users', $columns);
        if (\In2code\Luxletter\Utility\ConfigurationUtility::isMultiLanguageModeActivated()) {
            ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'luxletter_language', '', 'after:image');
        }
    }
);

<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(
    function () {
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
    }
);

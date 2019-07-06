<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(
    function () {
        $columns = [
            'luxletter_receiver' => [
                'exclude' => true,
                'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:fe_groups.luxletter_receiver',
                'config' => [
                    'type' => 'check'
                ]
            ]
        ];
        ExtensionManagementUtility::addTCAcolumns('fe_groups', $columns);
        ExtensionManagementUtility::addToAllTCAtypes('fe_groups', 'luxletter_receiver', '', 'after:subgroup');
    }
);

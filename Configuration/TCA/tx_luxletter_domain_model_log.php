<?php
use In2code\Luxletter\Domain\Model\Log;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:' . Log::TABLE_NAME,
        'label' => 'status',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'default_sortby' => 'ORDER BY crdate DESC',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:luxletter/Resources/Public/Icons/' . Log::TABLE_NAME . '.svg',
        'rootLevel' => -1
    ],
    'interface' => [
        'showRecordFieldList' => 'crdate,newsletter,user,status,properties',
    ],
    'types' => [
        '1' => ['showitem' => 'crdate,newsletter,user,status,properties'],
    ],
    'columns' => [
        'crdate' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:' . Log::TABLE_NAME . '.crdate',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'datetime',
                'readOnly' => true
            ]
        ],
        'newsletter' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Log::TABLE_NAME . '.newsletter',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => \In2code\Luxletter\Domain\Model\Newsletter::TABLE_NAME,
                'size' => 1,
                'maxitems' => 1,
                'multiple' => 0,
                'default' => 0,
                'readOnly' => true
            ]
        ],
        'user' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Log::TABLE_NAME . '.user',
            'config' => [
                'type' => 'group',
                'internal_type' => 'db',
                'allowed' => \In2code\Luxletter\Domain\Model\User::TABLE_NAME,
                'size' => 1,
                'maxitems' => 1,
                'multiple' => 0,
                'default' => 0,
                'readOnly' => true
            ]
        ],
        'status' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:' . Log::TABLE_NAME . '.status',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => true
            ]
        ],
        'properties' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Log::TABLE_NAME . '.properties',
            'config' => [
                'type' => 'text',
                'cols' => 32,
                'rows' => 5,
                'readOnly' => true
            ]
        ]
    ]
];

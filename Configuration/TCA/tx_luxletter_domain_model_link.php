<?php
use In2code\Luxletter\Domain\Model\Link;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:' . Link::TABLE_NAME,
        'label' => 'target',
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
        'iconfile' => 'EXT:luxletter/Resources/Public/Icons/' . Link::TABLE_NAME . '.svg',
        'rootLevel' => -1
    ],
    'interface' => [
        'showRecordFieldList' => 'newsletter,user,hash,target',
    ],
    'types' => [
        '1' => ['showitem' => 'newsletter,user,hash,target'],
    ],
    'columns' => [
        'newsletter' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Link::TABLE_NAME . '.newsletter',
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
                . Link::TABLE_NAME . '.user',
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
        'hash' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:' . Link::TABLE_NAME . '.hash',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'readOnly' => true
            ]
        ],
        'target' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Link::TABLE_NAME . '.target',
            'config' => [
                'type' => 'text',
                'cols' => 32,
                'rows' => 5,
                'readOnly' => true
            ]
        ]
    ]
];

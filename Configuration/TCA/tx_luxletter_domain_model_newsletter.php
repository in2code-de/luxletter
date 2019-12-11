<?php
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\Usergroup;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:' . Newsletter::TABLE_NAME,
        'label' => 'title',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'default_sortby' => 'ORDER BY title ASC',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:luxletter/Resources/Public/Icons/' . Newsletter::TABLE_NAME . '.svg',
        'rootLevel' => -1
    ],
    'interface' => [
        'showRecordFieldList' => 'disabled,title,description,datetime,subject,receiver,origin,bodytext,configuration_id',
    ],
    'types' => [
        '1' => ['showitem' => 'disabled,configuration_id,title,description,datetime,subject,receiver,origin,bodytext'],
    ],
    'columns' => [
        'disabled' => [
            'exclude' => true,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
            ],
        ],
        'title' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Newsletter::TABLE_NAME . '.title',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],
        'description' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Newsletter::TABLE_NAME . '.description',
            'config' => [
                'type' => 'text',
                'cols' => 500,
                'rows' => 3,
                'readOnly' => true
            ]
        ],
        'datetime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Newsletter::TABLE_NAME . '.datetime',
            'config' => [
                'type' => 'input',
                'size' => 13,
                'max' => 20,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
                ],
                'readOnly' => true
            ]
        ],
        'subject' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Newsletter::TABLE_NAME . '.subject',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],
        'receiver' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Newsletter::TABLE_NAME . '.receiver',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => Usergroup::TABLE_NAME,
                'foreign_table_where' => 'AND 1',
                'default' => 0,
                'readOnly' => true
            ]
        ],
        'origin' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Newsletter::TABLE_NAME . '.origin',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ]
        ],
        'bodytext' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Newsletter::TABLE_NAME . '.bodytext',
            'config' => [
                'type' => 'text',
                'cols' => 800,
                'rows' => 3,
                'readOnly' => true
            ]
        ],
        'configuration_id' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Newsletter::TABLE_NAME . '.configuration_id',
            'config' => [
                'type' => 'input',
                'readOnly' => true
            ],
        ],
    ],
];

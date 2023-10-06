<?php

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Domain\Model\User;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:' . Queue::TABLE_NAME,
        'label' => 'email',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'default_sortby' => 'ORDER BY datetime ASC',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'iconfile' => 'EXT:luxletter/Resources/Public/Icons/' . Queue::TABLE_NAME . '.svg',
        'rootLevel' => -1,
        'hideTable' => 1,
    ],
    'types' => [
        '1' => ['showitem' => 'email,newsletter,bodytext,user,datetime,sent,failures'],
    ],
    'columns' => [
        'email' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:' . Queue::TABLE_NAME . '.email',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
        'newsletter' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Queue::TABLE_NAME . '.newsletter',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => Newsletter::TABLE_NAME,
                'foreign_table_where' => 'AND sys_language_uid in (0,-1)',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'bodytext' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Newsletter::TABLE_NAME . '.bodytext',
            'config' => [
                'type' => 'text',
                'cols' => 800,
                'rows' => 3,
                'readOnly' => true,
                'default' => '',
            ],
        ],
        'user' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Queue::TABLE_NAME . '.user',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => User::TABLE_NAME,
                'foreign_table_where' => 'AND 1',
                'default' => 0,
                'readOnly' => true,
            ],
        ],
        'datetime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Queue::TABLE_NAME . '.datetime',
            'config' => [
                'type' => 'input',
                'size' => 13,
                'eval' => 'datetime',
                'checkbox' => 0,
                'default' => 0,
                'range' => [
                    'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y')),
                ],
                'readOnly' => true,
                'renderType' => 'inputDateTime',
            ],
        ],
        'sent' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:' . Queue::TABLE_NAME . '.sent',
            'config' => [
                'type' => 'check',
                'readOnly' => true,
            ],
        ],
        'failures' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:' . Queue::TABLE_NAME . '.failures',
            'config' => [
                'type' => 'input',
                'readOnly' => true,
            ],
        ],
    ],
];

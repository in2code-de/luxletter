<?php
use In2code\Luxletter\Domain\Model\Configuration;
use In2code\Luxletter\Tca\SiteSelection;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:' . Configuration::TABLE_NAME,
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
        'iconfile' => 'EXT:luxletter/Resources/Public/Icons/' . Configuration::TABLE_NAME . '.svg',
        'rootLevel' => -1
    ],
    'types' => [
        '1' => ['showitem' => 'title,from_email,from_name,reply_email,reply_name,site'],
    ],
    'columns' => [
        'title' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Configuration::TABLE_NAME . '.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'placeholder' => 'Marketing yourdomain.org',
                'eval' => 'required'
            ]
        ],
        'from_email' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Configuration::TABLE_NAME . '.from_email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'placeholder' => 'marketing@yourdomain.org',
                'eval' => 'required,email'
            ]
        ],
        'from_name' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Configuration::TABLE_NAME . '.from_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'placeholder' => 'Marketing',
                'eval' => 'required'
            ]
        ],
        'reply_email' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Configuration::TABLE_NAME . '.reply_email',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'placeholder' => 'sales@yourdomain.org',
                'eval' => 'required,email'
            ]
        ],
        'reply_name' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Configuration::TABLE_NAME . '.reply_name',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'placeholder' => 'Sales',
                'eval' => 'required'
            ]
        ],
        'site' => [
            'exclude' => true,
            'label' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:'
                . Configuration::TABLE_NAME . '.site',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'itemsProcFunc' => SiteSelection::class . '->getAll',
                'itemsProcConfig' => [
                    'table' => 'tt_content'
                ],
            ]
        ]
    ]
];

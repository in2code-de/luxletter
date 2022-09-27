<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(
    function () {
        $llPath = 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:';

        /**
         * Add new page doktype
         */
        if (\In2code\Luxletter\Utility\ConfigurationUtility::isMultiLanguageModeActivated()) {
            $doktype = \In2code\Luxletter\Domain\Repository\PageRepository::DOKTYPE_LUXLETTER;
            $doktypeDefault = \TYPO3\CMS\Core\Domain\Repository\PageRepository::DOKTYPE_DEFAULT;

            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
                'pages',
                'doktype',
                [
                    'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:pages.doktype_luxletter',
                    $doktype,
                    'EXT:luxletter/Resources/Public/Icons/luxletter_doktype.svg',
                ],
                '6',
                'after'
            );

            \TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule(
                $GLOBALS['TCA']['pages'],
                [
                    // add icon for new page type:
                    'ctrl' => [
                        'typeicon_classes' => [
                            $doktype => 'apps-pagetree-luxletter',
                            $doktype . '-contentFromPid' => 'apps-pagetree-luxletter-contentFromPid',
                            $doktype . '-root' => 'apps-pagetree-luxletter-root',
                            $doktype . '-hideinmenu' => 'apps-pagetree-luxletter-hideinmenu',
                        ],
                    ],
                    // add all page standard fields and tabs to your new page type
                    'types' => [
                        $doktype => [
                            'showitem' => $GLOBALS['TCA']['pages']['types'][$doktypeDefault]['showitem'],
                        ],
                    ],
                ]
            );

            $columns = [
                'luxletter_subject' => [
                    'exclude' => true,
                    'label' => $llPath . 'pages.luxletter_subject',
                    'displayCond' => 'FIELD:doktype:=:' . $doktype,
                    'config' => [
                        'type' => 'input',
                        'placeholder' => 'Your new newsletter for product A (2022/01)',
                        'description' => $llPath . 'module.newsletter.new.field.subject.description',
                        'default' => '',
                        'eval' => 'trim,required',
                    ],
                ],
            ];
            ExtensionManagementUtility::addTCAcolumns('pages', $columns);
            ExtensionManagementUtility::addToAllTCAtypes(
                'pages',
                '--div--;Luxletter,luxletter_subject',
                '',
                'after:rowDescription'
            );
        }
    }
);

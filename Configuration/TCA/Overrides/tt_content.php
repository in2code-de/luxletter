<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

call_user_func(
    function () {
        $languageFilePrefix = 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:';
        $frontendLanguageFilePrefix = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:';

        /**
         * Register plugins
         */
        ExtensionUtility::registerPlugin(
            'luxletter',
            'Fe',
            $languageFilePrefix . 'flexform.fe',
            'extension-lux',
            'plugins',
            $languageFilePrefix . 'flexform.fe.description'
        );
        ExtensionUtility::registerPlugin(
            'luxletter',
            'Unsubscribe2',
            $languageFilePrefix . 'flexform.unsubscribe2',
            'extension-lux',
            'plugins',
            $languageFilePrefix . 'flexform.unsubscribe2.description'
        );

        /**
         * Show fields and flexform for plugins
         */
        $pluginShowitem = '
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                --palette--;;general,
                --palette--;;headers,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:plugin,
                pi_flexform,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
                --palette--;;frames,
                --palette--;;appearanceLinks,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,
                --palette--;;language,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
                --palette--;;hidden,
                --palette--;;access,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,
                categories,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,
                rowDescription,
            --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended,';
        $GLOBALS['TCA']['tt_content']['types']['luxletter_fe']['showitem'] = $pluginShowitem;
        ExtensionManagementUtility::addPiFlexFormValue(
            '*',
            'FILE:EXT:luxletter/Configuration/FlexForm/FlexFormFe.xml',
            'luxletter_fe'
        );
        $GLOBALS['TCA']['tt_content']['types']['luxletter_unsubscribe2']['showitem'] = $pluginShowitem;
        ExtensionManagementUtility::addPiFlexFormValue(
            '*',
            'FILE:EXT:luxletter/Configuration/FlexForm/FlexFormUnsubscribe2.xml',
            'luxletter_unsubscribe2'
        );

        /**
         * Register new CType Teaser
         */
        ExtensionManagementUtility::addTcaSelectItem(
            'tt_content',
            'CType',
            [
                $languageFilePrefix . 'ctype.teaser',
                'teaser',
                'teaser',
            ]
        );

        /**
         * Manipulate tt_content TCA
         */
        $tca = [
            'ctrl' => [
                'typeicons' => [
                    'teaser' => 'teaser',
                ],
                'typeicon_classes' => [
                    'teaser' => 'teaser',
                ],
            ],
            'types' => [
                'teaser' => [
                    'showitem' => '
                        --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
                        --palette--;;general,
                        header;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:header.ALT.html_formlabel,
                        pi_flexform,
                        --div--;' . $frontendLanguageFilePrefix . 'tabs.appearance,
                        --palette--;' . $frontendLanguageFilePrefix . 'palette.appearanceLinks;appearanceLinks,
                        --div--;' . $frontendLanguageFilePrefix . 'tabs.access,
                        --palette--;' . $frontendLanguageFilePrefix . 'palette.hidden;hidden,
                        --palette--;' . $frontendLanguageFilePrefix . 'palette.visibility;visibility,
                        --palette--;' . $frontendLanguageFilePrefix . 'palette.access;access,',
                ],
            ],
        ];
        $GLOBALS['TCA']['tt_content'] = array_replace_recursive($GLOBALS['TCA']['tt_content'], $tca);

        /**
         * Include Flexform for teaser content element
         */
        ExtensionManagementUtility::addPiFlexFormValue(
            '*',
            'FILE:EXT:luxletter/Configuration/FlexForm/FlexFormCeTeaser.xml',
            'teaser'
        );
    }
);

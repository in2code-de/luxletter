<?php
call_user_func(
    function () {
        $languageFilePrefix = 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:';
        $frontendLanguageFilePrefix = 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:';

        /**
         * Register Plugins
         */
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin('luxletter', 'Fe', 'Luxletter: Unsubscribe');

        /**
         * Disable not needed fields in tt_content
         */
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['luxletter_fe']
            = 'select_key,pages,recursive';

        /**
         * Include Flexform for plugin
         */
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['luxletter_fe'] = 'pi_flexform';
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
            'luxletter_fe',
            'FILE:EXT:luxletter/Configuration/FlexForm/FlexFormFe.xml'
        );

        /**
         * Register new CType Teaser
         */
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTcaSelectItem(
            'tt_content',
            'CType',
            [
                $languageFilePrefix . 'ctype.teaser',
                'teaser',
                'teaser'
            ]
        );

        /**
         * Manipulate tt_content TCA
         */
        $tca = [
            'ctrl' => [
                'typeicons' => [
                    'teaser' => 'teaser'
                ],
                'typeicon_classes' => [
                    'teaser' => 'teaser'
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
                        --palette--;' . $frontendLanguageFilePrefix . 'palette.access;access,'
                ]
            ]
        ];
        $GLOBALS['TCA']['tt_content'] = array_replace_recursive($GLOBALS['TCA']['tt_content'], $tca);

        /**
         * Include Flexform for teaser content element
         */
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
            '*',
            'FILE:EXT:luxletter/Configuration/FlexForm/FlexFormCeTeaser.xml',
            'teaser'
        );
    }
);

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
        $GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['luxletter_fe'] = 'select_key,pages,recursive';

        /**
         * Include Flexform
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
                    'teaser' => 'ctype-teaser'
                ],
                'typeicon_classes' => [
                    'teaser' => 'ctype-teaser'
                ],
            ],
            'types' => [
                'teaser' => [
                    'showitem' => '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,' .
                        '--palette--;;general,header;' . $frontendLanguageFilePrefix .
                        'header.ALT.shortcut_formlabel,records;' . $frontendLanguageFilePrefix .
                        'records_formlabel,--div--;' . $frontendLanguageFilePrefix .
                        'tabs.appearance,--palette--;;frames,--palette--;;appearanceLinks,' .
                        '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language,' .
                        '--palette--;;language,--div--;LLL:EXT:core/Resources/Private/Language/Form/' .
                        'locallang_tabs.xlf:access,--palette--;;hidden,--palette--;;access,--div--;' .
                        'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:categories,--div--;' .
                        'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category,' .
                        'categories,--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:notes,' .
                        'rowDescription,--div--;' .
                        'LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:extended'
                ]
            ]
        ];
        $GLOBALS['TCA']['tt_content'] = array_replace_recursive($GLOBALS['TCA']['tt_content'], $tca);
    }
);

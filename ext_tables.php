<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(
    function () {

        /**
         * Register Icons
         */
        $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Imaging\IconRegistry::class
        );
        $iconRegistry->registerIcon(
            'extension-lux-module',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:luxletter/Resources/Public/Icons/lux_white.svg']
        );

        /**
         * Include Modules
         */
        // Add Main module "LUX" - shared with EXT:lux and EXT:luxenterprise (if installed)
        // Acces to a main module is implicit, as soon as a user has access to at least one of its submodules.
        if (empty($GLOBALS['TBE_MODULES']['lux'])) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule(
                'lux',
                '',
                '',
                null,
                [
                    'name' => 'lux',
                    'labels' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_mod.xlf',
                    'iconIdentifier' => 'extension-lux-module'
                ]
            );
        }
        // Add module for analysis
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
            'In2code.luxletter',
            'lux',
            'luxletter',
            '',
            [
                'Newsletter' => 'dashboard, list, new, create, enable, disable, delete',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:luxletter/Resources/Public/Icons/lux_module_newsletter.svg',
                'labels' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_mod_newsletter.xlf',
            ]
        );

        /**
         * Add TypoScript Static Template
         */
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            'luxletter',
            'Configuration/TypoScript/',
            'Main TypoScript'
        );
    }
);

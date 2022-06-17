<?php
if (!defined('TYPO3')) {
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
            'extension-luxletter',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:luxletter/Resources/Public/Icons/Extension.svg']
        );
        $iconRegistry->registerIcon(
            'extension-lux-module',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:luxletter/Resources/Public/Icons/lux_white.svg']
        );
        $iconRegistry->registerIcon(
            'teaser',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:luxletter/Resources/Public/Icons/ctype-teaser.svg']
        );
        $iconRegistry->registerIcon(
            'luxletter-widget-receiver',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:luxletter/Resources/Public/Icons/widget_receiver.svg']
        );
        $iconRegistry->registerIcon(
            'luxletter-widget-newsletter',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:luxletter/Resources/Public/Icons/widget_newsletter.svg']
        );
        $iconRegistry->registerIcon(
            'apps-pagetree-luxletter',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:luxletter/Resources/Public/Icons/luxletter_doktype.svg']
        );
        $iconRegistry->registerIcon(
            'apps-pagetree-luxletter-contentFromPid',
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:luxletter/Resources/Public/Icons/luxletter_doktype.svg']
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
            'Luxletter',
            'lux',
            'luxletter',
            '',
            [
                \In2code\Luxletter\Controller\NewsletterController::class =>
                    'dashboard, list, new, create, enable, disable, delete, receiver',
            ],
            [
                'access' => 'user,group',
                'icon' => 'EXT:luxletter/Resources/Public/Icons/lux_module_newsletter.svg',
                'labels' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_mod_newsletter.xlf',
            ]
        );

        /**
         * Add static page TSconfig
         */
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:luxletter/Configuration/PageTSConfig/ContentElements.typoscript">'
        );

        /**
         * Add new page doktype
         */
        if (\In2code\Luxletter\Utility\ConfigurationUtility::isMultiLanguageModeActivated()) {
            $doktype = \In2code\Luxletter\Domain\Repository\PageRepository::DOKTYPE_LUXLETTER;
            $GLOBALS['PAGES_TYPES'][$doktype] = [
                'type' => 'web',
                'allowedTables' => '*',
            ];
            // Allow backend users to drag and drop the new page type:
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addUserTSConfig(
                'options.pageTree.doktypesToShowInNewPageDragArea := addToList(' . $doktype . ')'
            );
        }
    }
);

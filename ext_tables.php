<?php

declare(strict_types=1);

use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

defined('TYPO3') || die();

/**
 * Register Icons
 */
$iconRegistry = GeneralUtility::makeInstance(
    IconRegistry::class
);
$iconRegistry->registerIcon(
    'extension-lux',
    SvgIconProvider::class,
    ['source' => 'EXT:luxletter/Resources/Public/Icons/lux.svg']
);
$iconRegistry->registerIcon(
    'extension-luxletter',
    SvgIconProvider::class,
    ['source' => 'EXT:luxletter/Resources/Public/Icons/Extension.svg']
);
$iconRegistry->registerIcon(
    'extension-lux-module',
    SvgIconProvider::class,
    ['source' => 'EXT:luxletter/Resources/Public/Icons/lux_white.svg']
);
$iconRegistry->registerIcon(
    'extension-luxletter-module',
    SvgIconProvider::class,
    ['source' => 'EXT:luxletter/Resources/Public/Icons/lux_module_newsletter.svg']
);
$iconRegistry->registerIcon(
    'extension-luxletter-star',
    SvgIconProvider::class,
    ['source' => 'EXT:luxletter/Resources/Public/Icons/star.svg']
);
$iconRegistry->registerIcon(
    'teaser',
    SvgIconProvider::class,
    ['source' => 'EXT:luxletter/Resources/Public/Icons/ctype-teaser.svg']
);
$iconRegistry->registerIcon(
    'luxletter-widget-receiver',
    SvgIconProvider::class,
    ['source' => 'EXT:luxletter/Resources/Public/Icons/widget_receiver.svg']
);
$iconRegistry->registerIcon(
    'luxletter-widget-newsletter',
    SvgIconProvider::class,
    ['source' => 'EXT:luxletter/Resources/Public/Icons/widget_newsletter.svg']
);
$iconRegistry->registerIcon(
    'apps-pagetree-luxletter',
    SvgIconProvider::class,
    ['source' => 'EXT:luxletter/Resources/Public/Icons/luxletter_doktype.svg']
);
$iconRegistry->registerIcon(
    'apps-pagetree-luxletter-contentFromPid',
    SvgIconProvider::class,
    ['source' => 'EXT:luxletter/Resources/Public/Icons/luxletter_doktype.svg']
);

/**
 * Add static page TSconfig
 */
ExtensionManagementUtility::addPageTSConfig(
    '@import "EXT:luxletter/Configuration/PageTSConfig/ContentElements.typoscript"'
);

/**
 * Add new page doktype
 */
if (ConfigurationUtility::isMultiLanguageModeActivated()) {
    $doktype = ConfigurationUtility::getMultilanguageNewsletterPageDoktype();
    $GLOBALS['PAGES_TYPES'][$doktype] = [
        'type' => 'web',
        'allowedTables' => '*',
    ];
    // Allow backend users to drag and drop the new page type:
    ExtensionManagementUtility::addUserTSConfig(
        'options.pageTree.doktypesToShowInNewPageDragArea := addToList(' . $doktype . ')'
    );
}

<?php

declare(strict_types=1);

defined('TYPO3') || die();

/**
 * Include Frontend Plugins
 */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Luxletter',
    'Fe',
    [\In2code\Luxletter\Controller\FrontendController::class => 'unsubscribe'],
    [\In2code\Luxletter\Controller\FrontendController::class => 'unsubscribe'],
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Luxletter',
    'Unsubscribe2',
    [\In2code\Luxletter\Controller\FrontendController::class => 'unsubscribe2,unsubscribe2Update'],
    [\In2code\Luxletter\Controller\FrontendController::class => 'unsubscribe2,unsubscribe2Update'],
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Luxletter',
    'Preview',
    [\In2code\Luxletter\Controller\FrontendController::class => 'preview'],
    [\In2code\Luxletter\Controller\FrontendController::class => 'preview'],
);
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'Luxletter',
    'TrackingPixel',
    [\In2code\Luxletter\Controller\FrontendController::class => 'trackingPixel'],
    [\In2code\Luxletter\Controller\FrontendController::class => 'trackingPixel'],
);

/**
 * Fluid Namespace
 */
$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['luxletter'][] = 'In2code\Luxletter\ViewHelpers';

/**
 * Update Wizards
 */
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['luxletterReceiversUpdateWizard']
    = \In2code\Luxletter\Update\LuxletterReceiversUpdateWizard::class;

/**
 * Add TypoScript automatically (to use it in backend modules)
 */
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
    'Luxletter',
    'setup',
    '@import "EXT:luxletter/Configuration/TypoScript/Basic/setup.typoscript"'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
    'Luxletter',
    'constants',
    '@import "EXT:luxletter/Configuration/TypoScript/Basic/constants.typoscript"'
);

/**
 * CacheHash: Add LUX paramters to excluded variables
 */
\In2code\Luxletter\Utility\CacheHashUtility::addArgumentsToExcludedVariables();

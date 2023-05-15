<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(
    function () {

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
         * Add an absRefPrefix for FluidStyledMailContent to prefix images with absolute paths
         * Todo: Can be removed if TYPO3 11 support is dropped and should be replaced with `fluidStyledMailContent.config.forceAbsoluteUrls = 1`
         */
        if (\TYPO3\CMS\Core\Core\Environment::isCli() === false) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
                'luxletterFluidStyledMailContent',
                'setup',
                'fluidStyledMailContent.config.absRefPrefix = '
                . \In2code\Luxletter\Utility\ConfigurationUtility::getCurrentDomain()
            );
        }

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
    }
);

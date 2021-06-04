<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(
    function () {

        /**
         * Include Frontend Plugins
         */
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'In2code.luxletter',
            'Fe',
            [
                'Frontend' => 'unsubscribe,preview,trackingPixel'
            ],
            [
                'Frontend' => 'unsubscribe,preview,trackingPixel'
            ]
        );

        /**
         * Fluid Namespace
         */
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['luxletter'][] = 'In2code\Luxletter\ViewHelpers';

        /**
         * Add an absRefPrefix for FluidStyledMailContent (could be overruled by site configuration)
         */
        if (\TYPO3\CMS\Core\Core\Environment::isCli() === false) {
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
                'luxletterFluidStyledMailContent',
                'setup',
                'fluidStyledMailContent.config.absRefPrefix = '
                . \In2code\Luxletter\Utility\ConfigurationUtility::getCurrentDomain()
            );
        }
    }
);

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
         * Add an absRefPrefix for FluidStyledMailContent (but absRefPrefix will be overruled by site configuration)
         */
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
            'luxletterFluidStyledMailContent',
            'setup',
            'fluidStyledMailContent.config.absRefPrefix = '
            . \In2code\Luxletter\Utility\ConfigurationUtility::getDomain() . '/'
        );
    }
);

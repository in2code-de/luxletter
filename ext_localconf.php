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
    }
);

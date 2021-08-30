<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

call_user_func(
    static function () {
        /**
         * Add TypoScript Static Template
         */
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            'luxletter',
            'Configuration/TypoScript/Basic/',
            'Basic TypoScript'
        );
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            'luxletter',
            'Configuration/TypoScript/FluidStyledMailContent/',
            'FluidStyledMailContent'
        );
    }
);

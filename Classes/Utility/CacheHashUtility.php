<?php

declare(strict_types=1);
namespace In2code\Luxletter\Utility;

class CacheHashUtility
{
    /**
     * Variables to be excluded from cHash checks
     *
     * @var array|string[]
     */
    protected static array $excludedVariables = [
        'tx_luxletter_preview[origin]',
        'tx_luxletter_preview[layout]',
        'tx_luxletter_preview[language]',
        'tx_luxletter_trackingpixel[user]',
        'tx_luxletter_trackingpixel[newsletter]',
    ];

    public static function addArgumentsToExcludedVariables(): void
    {
        if (!isset($GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'])) {
            $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'] = [];
        }
        $excludedParameters = &$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'];
        $excludedParameters = array_merge($excludedParameters, self::$excludedVariables);
    }
}

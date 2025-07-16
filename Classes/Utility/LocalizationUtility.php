<?php

declare(strict_types=1);
namespace In2code\Luxletter\Utility;

use TYPO3\CMS\Extbase\Utility\LocalizationUtility as LocalizationUtilityExtbase;

/**
 * Class LocalizationUtility
 */
class LocalizationUtility
{
    /**
     * @param string $key
     * @param string $extensionName
     * @param array|null $arguments
     * @return string|null
     */
    public static function translate(string $key, string $extensionName = 'Luxletter', ?array $arguments = null)
    {
        $label = LocalizationUtilityExtbase::translate($key, $extensionName, $arguments);
        if (empty($label)) {
            $label = $key;
        }
        return $label;
    }

    /**
     * @param string $key
     * @param array|null $arguments
     * @return string|null
     */
    public static function translateByKey(string $key, ?array $arguments = null)
    {
        $locallangPrefix = 'LLL:EXT:luxletter/Resources/Private/Language/locallang.xlf:';
        try {
            return self::translate($locallangPrefix . $key, 'Lux', $arguments);
        } catch (\Exception $exception) {
            // Use this part for unit testing
            return $key;
        }
    }
}

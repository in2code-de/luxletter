<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Utility;

use In2code\Luxletter\Exception\MisconfigurationException;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class StringUtility
 */
class StringUtility
{
    /**
     * @param string $value
     * @return bool
     */
    public static function isValidUrl($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return stristr($haystack, $needle) && strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    /**
     * Check if string ends with another string
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return stristr($haystack, $needle) !== false && substr($haystack, (strlen($needle) * -1)) === $needle;
    }

    /**
     * @param array $arguments
     * @param bool $useEncryptionKey can be disabled for testing
     * @return string
     * @throws MisconfigurationException
     * @throws Exception
     */
    public static function getHashFromArguments(array $arguments, bool $useEncryptionKey = true): string
    {
        if ($useEncryptionKey === true) {
            $arguments = array_merge($arguments, [ConfigurationUtility::getEncryptionKey()]);
        }
        return hash('sha256', implode('/', $arguments));
    }
}

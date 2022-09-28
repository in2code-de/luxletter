<?php

declare(strict_types=1);
namespace In2code\Luxletter\Utility;

use In2code\Luxletter\Exception\MisconfigurationException;

/**
 * Class StringUtility
 */
class StringUtility
{
    /**
     * @param string $value
     * @return bool
     */
    public static function isAbsoluteImageUrl(string $value): bool
    {
        $imageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'tif', 'tiff', 'webp', 'svg'];
        return self::isValidUrl($value)
            && in_array(FileUtility::getExtensionFromPathAndFilename($value), $imageExtensions);
    }

    /**
     * Checks for a valid and absolute URL (e.g. "https://domain.org" or "ssh://something")
     *
     * @param string $value
     * @return bool
     */
    public static function isValidUrl(string $value): bool
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
     */
    public static function getHashFromArguments(array $arguments, bool $useEncryptionKey = true): string
    {
        if ($useEncryptionKey === true) {
            $arguments = array_merge($arguments, [ConfigurationUtility::getEncryptionKey()]);
        }
        return hash('sha256', implode('/', $arguments));
    }

    /**
     * @param string $string
     * @param string $postfix
     * @return string
     */
    public static function removeStringPostfix(string $string, string $postfix): string
    {
        return preg_replace('~' . $postfix . '$~', '', $string);
    }
}

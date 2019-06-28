<?php
declare(strict_types=1);
namespace In2code\Luxletter\Utility;

/**
 * Class StringUtility
 */
class StringUtility
{

    /**
     * Test string if it is an URL
     *
     * @param string $value
     * @return bool
     */
    public static function isValidUrl($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param array $arguments
     * @return string
     */
    public static function getHashFromArguments(array $arguments): string
    {
        return hash('sha256', implode('/', $arguments));
    }
}

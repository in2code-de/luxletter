<?php

declare(strict_types=1);
namespace In2code\Luxletter\Utility;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Class BackendUserUtility
 */
class BackendUserUtility
{
    /**
     * @return bool
     */
    public static function isBackendUserAuthenticated(): bool
    {
        return self::getBackendUserAuthentication() !== null;
    }

    /**
     * @param string $key
     * @param string $action
     * @param string $controller
     * @param array $data
     * @return void
     */
    public static function saveValueToSession(string $key, string $action, string $controller, array $data): void
    {
        self::getBackendUserAuthentication()->setAndSaveSessionData($key . $action . $controller . '_luxletter', $data);
    }

    /**
     * @param string $key
     * @param string $action
     * @param string $controller
     * @return array
     */
    public static function getSessionValue(string $key, string $action, string $controller): array
    {
        return (array)self::getBackendUserAuthentication()->getSessionData($key . $action . $controller . '_luxletter');
    }

    /**
     * @return BackendUserAuthentication|null
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getBackendUserAuthentication(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}

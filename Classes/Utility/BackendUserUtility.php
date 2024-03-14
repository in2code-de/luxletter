<?php

declare(strict_types=1);
namespace In2code\Luxletter\Utility;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class BackendUserUtility
{
    public static function isBackendUserAuthenticated(): bool
    {
        return self::getBackendUserAuthentication() !== null;
    }

    public static function isAdministrator(): bool
    {
        if (self::getBackendUserAuthentication() !== null) {
            return self::getBackendUserAuthentication()->isAdmin();
        }
        return false;
    }

    public static function saveValueToSession(string $key, string $action, string $controller, array $data): void
    {
        self::getBackendUserAuthentication()->setAndSaveSessionData($key . $action . $controller . '_luxletter', $data);
    }

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
        return $GLOBALS['BE_USER'] ?? null;
    }
}

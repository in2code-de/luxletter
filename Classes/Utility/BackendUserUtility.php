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
    public static function isBackendUserAuthenticated()
    {
        return self::getBackendUserAuthentication() !== null;
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

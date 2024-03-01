<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Utility\BackendUserUtility;
use TYPO3\CMS\Core\Type\Bitmask\Permission;

trait PermissionTrait
{
    private function isAuthenticated(array $pageRecord): bool
    {
        if (BackendUserUtility::isAdministrator()) {
            return true;
        }

        $beuserAuthentication = BackendUserUtility::getBackendUserAuthentication();
        return $beuserAuthentication !== null &&
            $beuserAuthentication->doesUserHaveAccess($pageRecord, Permission::PAGE_SHOW);
    }
}

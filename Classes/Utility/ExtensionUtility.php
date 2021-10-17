<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Utility;

use TYPO3\CMS\Core\Package\Exception;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Class ExtensionUtility
 */
class ExtensionUtility
{
    /**
     * @param string $minimumVersion if set check for minimum version like "5.0.0"
     * @return bool
     * @throws Exception
     */
    public static function isLuxAvailable(string $minimumVersion = ''): bool
    {
        if ($minimumVersion === '') {
            return ExtensionManagementUtility::isLoaded('lux');
        }
        $versionNumberLux = VersionNumberUtility::convertVersionNumberToInteger(
            ExtensionManagementUtility::getExtensionVersion('lux')
        );
        return $versionNumberLux >= VersionNumberUtility::convertVersionNumberToInteger($minimumVersion);
    }
}

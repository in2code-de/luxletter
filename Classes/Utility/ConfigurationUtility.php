<?php
declare(strict_types=1);
namespace In2code\Luxletter\Utility;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ConfigurationUtility
 */
class ConfigurationUtility
{

    /**
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getDomain(): string
    {
        return (string)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('luxletter', 'domain');
    }

    /**
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function isRewriteLinksInNewsletterActivated(): bool
    {
        return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(
            'luxletter',
            'rewriteLinksInNewsletter'
        ) === '1';
    }
}

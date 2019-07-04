<?php
declare(strict_types=1);
namespace In2code\Luxletter\Utility;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class ConfigurationUtility
 */
class ConfigurationUtility
{

    /**
     * Get TypoScript settings
     *
     * @return array
     * @throws InvalidConfigurationTypeException
     */
    public static function getExtensionSettings(): array
    {
        return ObjectUtility::getConfigurationManager()->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'luxletter'
        );
    }

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
     * @return int
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getPidUnsubscribe(): int
    {
        return (int)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('luxletter', 'pidUnsubscribe');
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

    /**
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getFromEmail(): string
    {
        return (string)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('luxletter', 'fromEmail');
    }

    /**
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getFromName(): string
    {
        return (string)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('luxletter', 'fromName');
    }

    /**
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getReplyEmail(): string
    {
        return (string)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('luxletter', 'replyEmail');
    }

    /**
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public static function getReplyName(): string
    {
        return (string)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('luxletter', 'replyName');
    }

    /**
     * @return string
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getEncryptionKey(): string
    {
        $configurationManager = ObjectUtility::getConfigurationManagerCore();
        $encryptionKey = $configurationManager->getLocalConfigurationValueByPath('SYS/encryptionKey');
        if (empty($encryptionKey)) {
            throw new \DomainException('No encryption key found in this TYPO3 installation', 1562069158);
        }
        return $encryptionKey;
    }
}

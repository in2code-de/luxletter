<?php
declare(strict_types=1);
namespace In2code\Luxletter\Utility;

use In2code\Luxletter\Exception\MisconfigurationException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\Exception;

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
     * @throws Exception
     */
    public static function getExtensionSettings(): array
    {
        return ObjectUtility::getConfigurationManager()->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'luxletter'
        );
    }

    /**
     * @return string like "https://www.luxletter.de" without trailing slash
     */
    public static function getDomain(): string
    {
        $domain = (string)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('luxletter', 'domain');
        return rtrim($domain, '/');
    }

    /**
     * @return int
     */
    public static function getPidUnsubscribe(): int
    {
        return (int)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('luxletter', 'pidUnsubscribe');
    }

    /**
     * @return bool
     */
    public static function isRewriteLinksInNewsletterActivated(): bool
    {
        return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(
                'luxletter',
                'rewriteLinksInNewsletter'
            ) === '1';
    }

    /**
     * @return int
     */
    public static function getTypeNumToNumberLocation(): int
    {
        return (int)GeneralUtility::makeInstance(ExtensionConfiguration::class)
            ->get('luxletter', 'addTypeNumToNumberLocation');
    }

    /**
     * @return bool
     */
    public static function isReceiverActionActivated(): bool
    {
        return GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('luxletter', 'receiverAction') === '1';
    }

    /**
     * @return string
     */
    public static function getFromEmail(): string
    {
        return (string)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('luxletter', 'fromEmail');
    }

    /**
     * @return string
     */
    public static function getFromName(): string
    {
        return (string)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('luxletter', 'fromName');
    }

    /**
     * @return string
     */
    public static function getReplyEmail(): string
    {
        return (string)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('luxletter', 'replyEmail');
    }

    /**
     * @return string
     */
    public static function getReplyName(): string
    {
        return (string)GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('luxletter', 'replyName');
    }

    /**
     * @return string
     * @throws MisconfigurationException
     * @throws Exception
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getEncryptionKey(): string
    {
        $configurationManager = ObjectUtility::getConfigurationManagerCore();
        $encryptionKey = $configurationManager->getLocalConfigurationValueByPath('SYS/encryptionKey');
        if (empty($encryptionKey)) {
            throw new MisconfigurationException('No encryption key found in this TYPO3 installation', 1562069158);
        }
        return $encryptionKey;
    }

    /**
     * @param string $versionToCompare like "1.2.3"
     * @return bool
     */
    public static function isVersionToCompareSameOrLowerThenCurrentTypo3Version(string $versionToCompare): bool
    {
        return VersionNumberUtility::convertVersionNumberToInteger($versionToCompare) <= self::getCurrentTypo3Version();
    }

    /**
     * Return current TYPO3 version as integer - e.g. 10003000 (10.3.0) or 9005014 (9.5.14)
     *
     * @return int
     */
    protected static function getCurrentTypo3Version(): int
    {
        return VersionNumberUtility::convertVersionNumberToInteger(VersionNumberUtility::getNumericTypo3Version());
    }
}

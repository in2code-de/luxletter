<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Utility;

use TYPO3\CMS\Core\Configuration\ConfigurationManager as ConfigurationManagerCore;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class ObjectUtility
 */
class ObjectUtility
{
    /**
     * @return ConfigurationManager
     * @codeCoverageIgnore
     */
    public static function getConfigurationManager(): ConfigurationManager
    {
        return GeneralUtility::makeInstance(ConfigurationManager::class);
    }

    /**
     * @return ConfigurationManagerCore
     */
    public static function getConfigurationManagerCore(): ConfigurationManagerCore
    {
        return GeneralUtility::makeInstance(ConfigurationManagerCore::class);
    }

    /**
     * @return ContentObjectRenderer
     * @codeCoverageIgnore
     */
    public static function getContentObject(): ContentObjectRenderer
    {
        return GeneralUtility::makeInstance(ContentObjectRenderer::class);
    }

    /**
     * @return JsonResponse
     */
    public static function getJsonResponse(): JsonResponse
    {
        return GeneralUtility::makeInstance(JsonResponse::class);
    }

    /**
     * @return LanguageService|null
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'];
    }
}

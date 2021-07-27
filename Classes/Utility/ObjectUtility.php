<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Utility;

use TYPO3\CMS\Core\Configuration\ConfigurationManager as ConfigurationManagerCore;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class ObjectUtility
 */
class ObjectUtility
{

    /**
     * @return ObjectManager
     */
    public static function getObjectManager(): ObjectManager
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        return $objectManager;
    }

    /**
     * @return ConfigurationManager
     * @throws Exception
     * @codeCoverageIgnore
     */
    public static function getConfigurationManager(): ConfigurationManager
    {
        return self::getObjectManager()->get(ConfigurationManager::class);
    }

    /**
     * @return ConfigurationManagerCore
     * @throws Exception
     */
    public static function getConfigurationManagerCore(): ConfigurationManagerCore
    {
        return self::getObjectManager()->get(ConfigurationManagerCore::class);
    }

    /**
     * @return ContentObjectRenderer
     * @throws Exception
     * @codeCoverageIgnore
     */
    public static function getContentObject(): ContentObjectRenderer
    {
        return self::getObjectManager()->get(ContentObjectRenderer::class);
    }

    /**
     * @return JsonResponse
     * @throws Exception
     */
    public static function getJsonResponse(): JsonResponse
    {
        return self::getObjectManager()->get(JsonResponse::class);
    }
}

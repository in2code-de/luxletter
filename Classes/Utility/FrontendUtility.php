<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class FrontendUtility
 */
class FrontendUtility
{

    /**
     * Get current scheme, domain and path of the current installation
     *
     * @return string like "https://www.domain.org/
     */
    public static function getCurrentUri(): string
    {
        $uri = '';
        $uri .= parse_url(GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'), PHP_URL_SCHEME);
        $uri .= '://' . GeneralUtility::getIndpEnv('HTTP_HOST') . '/';
        $uri .= rtrim(GeneralUtility::getIndpEnv('TYPO3_SITE_PATH'), '/');
        return $uri;
    }
    /**
     * @return int
     */
    public static function getCurrentPageIdentifier(): int
    {
        $tsfe = self::getTyposcriptFrontendController();
        if ($tsfe !== null) {
            return (int)$tsfe->id;
        }
        return 0;
    }

    /**
     * @return string
     */
    public static function getActionName(): string
    {
        $action = '';
        $plugin = self::getPluginName();
        $arguments = GeneralUtility::_GPmerged($plugin);
        if (!empty($arguments['action'])) {
            $action = $arguments['action'];
        }
        return $action;
    }

    /**
     * @return string
     */
    public static function getModuleName(): string
    {
        $module = '';
        $route = GeneralUtility::_GP('route');
        if (!empty($route)) {
            $module = rtrim(ltrim($route, '/lux/Luxletter'), '/');
        }
        return $module;
    }

    /**
     * @return string
     */
    public static function getPluginName(): string
    {
        return 'tx_luxletter_lux_luxletterluxletter';
    }

    /**
     * @return TypoScriptFrontendController
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected static function getTyposcriptFrontendController(): ?TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'] ?: null;
    }
}

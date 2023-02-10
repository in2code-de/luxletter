<?php

declare(strict_types=1);
namespace In2code\Luxletter\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class FrontendUtility
{
    public static function getCurrentPageIdentifier(): int
    {
        $tsfe = self::getTyposcriptFrontendController();
        if ($tsfe !== null) {
            return (int)$tsfe->id;
        }
        return 0;
    }

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

    public static function getModuleName(): string
    {
        $module = '';
        $route = GeneralUtility::_GP('route');
        if (!empty($route)) {
            $module = rtrim(ltrim($route, '/lux/Luxletter'), '/');
        }
        return $module;
    }

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

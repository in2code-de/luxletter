<?php
declare(strict_types=1);
namespace In2code\Luxletter\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FrontendUtility
 */
class FrontendUtility
{

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
}

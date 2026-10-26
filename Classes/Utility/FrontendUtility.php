<?php

declare(strict_types=1);
namespace In2code\Luxletter\Utility;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Page\PageInformation;

class FrontendUtility
{
    /**
     * @return int
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function getCurrentPageIdentifier(): int
    {
        $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
        if ($request instanceof ServerRequestInterface) {
            $pageInformation = $request->getAttribute('frontend.page.information');
            if ($pageInformation instanceof PageInformation) {
                return $pageInformation->getId();
            }
        }
        return 0;
    }

    public static function getActionName(): string
    {
        $action = '';
        $plugin = self::getPluginName();
        $arguments = $_REQUEST[$plugin] ?? [];
        if (!empty($arguments['action'])) {
            $action = $arguments['action'];
        }
        return $action;
    }

    public static function getModuleName(): string
    {
        $module = '';
        $route = $_REQUEST['route'] ?? '';
        if (!empty($route)) {
            $module = rtrim(ltrim($route, '/lux/Luxletter'), '/');
        }
        return $module;
    }

    public static function getPluginName(): string
    {
        return 'tx_luxletter_lux_luxletterluxletter';
    }
}

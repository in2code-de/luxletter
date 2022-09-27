<?php

declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Condition;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * IsLuxenterpriseExtensionActivatedViewHelper
 * @noinspection PhpUnused
 */
class IsLuxenterpriseExtensionActivatedViewHelper extends AbstractConditionViewHelper
{
    /**
     * @param null $arguments
     * @return bool
     */
    protected static function evaluateCondition($arguments = null): bool
    {
        return ExtensionManagementUtility::isLoaded('luxenterprise');
    }
}

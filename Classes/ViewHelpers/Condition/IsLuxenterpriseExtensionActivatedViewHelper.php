<?php

declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Condition;

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

class IsLuxenterpriseExtensionActivatedViewHelper extends AbstractConditionViewHelper
{
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext)
    {
        return ExtensionManagementUtility::isLoaded('luxenterprise');
    }
}

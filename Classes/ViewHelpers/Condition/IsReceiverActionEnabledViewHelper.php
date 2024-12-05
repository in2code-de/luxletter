<?php

declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Condition;

use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

class IsReceiverActionEnabledViewHelper extends AbstractConditionViewHelper
{
    public static function verdict(array $arguments, RenderingContextInterface $renderingContext)
    {
        return ConfigurationUtility::isReceiverActionActivated();
    }
}

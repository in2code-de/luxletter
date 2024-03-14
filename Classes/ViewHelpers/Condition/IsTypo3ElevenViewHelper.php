<?php

declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Condition;

use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * IsTypo3ElevenViewHelper
 * @noinspection PhpUnused
 * Todo: Can be removed when TYPO3 11 support is dropped
 */
class IsTypo3ElevenViewHelper extends AbstractConditionViewHelper
{
    protected static function evaluateCondition($arguments = null): bool
    {
        unset($arguments);
        return ConfigurationUtility::isTypo3Version12() === false;
    }
}

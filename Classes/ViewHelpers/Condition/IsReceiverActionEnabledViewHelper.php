<?php
declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Condition;

use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * Class IsReceiverActionEnabledViewHelper
 * @noinspection PhpUnused
 */
class IsReceiverActionEnabledViewHelper extends AbstractConditionViewHelper
{
    /**
     * @param null $arguments
     * @return bool
     * @throws \Exception
     */
    protected static function evaluateCondition($arguments = null): bool
    {
        return ConfigurationUtility::isReceiverActionActivated();
    }
}

<?php
declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Condition;

use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * Class IsTypo3VersionOrHigherViewHelper
 * Todo: This is needed as long as TYPO3 9 is supported
 * @noinspection PhpUnused
 */
class IsVersionToCompareSameOrLowerThenCurrentTypo3VersionViewHelper extends AbstractConditionViewHelper
{
    /**
     * Initializes the "then" and "else" arguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('version', 'string', 'e.g. "10.0.0"', true);
    }

    /**
     * @param null $arguments
     * @return bool
     */
    protected static function evaluateCondition($arguments = null): bool
    {
        return ConfigurationUtility::isVersionToCompareSameOrLowerThenCurrentTypo3Version($arguments['version']);
    }
}

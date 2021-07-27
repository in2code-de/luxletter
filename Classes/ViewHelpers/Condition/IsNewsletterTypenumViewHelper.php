<?php
declare(strict_types = 1);
namespace In2code\Luxletter\ViewHelpers\Condition;

use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * Class IsNewsletterTypenumViewHelper
 * @noinspection PhpUnused
 */
class IsNewsletterTypenumViewHelper extends AbstractConditionViewHelper
{
    /**
     * @param null $arguments
     * @return bool
     * @throws \Exception
     */
    protected static function evaluateCondition($arguments = null): bool
    {
        return ConfigurationUtility::getTypeNumToNumberLocation() === (int)GeneralUtility::_GP('type');
    }
}

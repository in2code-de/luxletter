<?php

declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Condition;

use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

/**
 * IsMultiLanguageModeActivatedViewHelper
 * @noinspection PhpUnused
 */
class IsMultiLanguageModeActivatedViewHelper extends AbstractConditionViewHelper
{
    /**
     * @param null $arguments
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected static function evaluateCondition($arguments = null): bool
    {
        return ConfigurationUtility::isMultiLanguageModeActivated();
    }
}

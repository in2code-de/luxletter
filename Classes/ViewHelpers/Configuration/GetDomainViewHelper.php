<?php
declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Configuration;

use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetDomainViewHelper
 */
class GetDomainViewHelper extends AbstractViewHelper
{

    /**
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function render(): string
    {
        return ConfigurationUtility::getDomain();
    }
}

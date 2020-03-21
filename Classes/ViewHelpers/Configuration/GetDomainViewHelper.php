<?php
declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Configuration;

use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetDomainViewHelper
 * @noinspection PhpUnused
 */
class GetDomainViewHelper extends AbstractViewHelper
{
    /**
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws MisconfigurationException
     */
    public function render(): string
    {
        return ConfigurationUtility::getDomain();
    }
}

<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\ConfigurationUtility;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Routing\InvalidRouteArgumentsException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FrontendUrlService
 */
class FrontendUrlService
{
    /**
     * @param int $pageIdentifier
     * @param array $arguments
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidRouteArgumentsException
     * @throws SiteNotFoundException
     * @throws MisconfigurationException
     */
    public function getTypolinkUrlFromParameter(int $pageIdentifier, array $arguments = []): string
    {
        /** @var Site $site */
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageIdentifier);
        $uri = $site->getRouter()->generateUri($pageIdentifier, $arguments);
        $url = $uri->__tostring();
        return $url;
    }

    /**
     * @param array $arguments
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws MisconfigurationException
     */
    public function getFrontendUrlFromParameter(array $arguments): string
    {
        $url = ConfigurationUtility::getDomain();
        $url .= '/?' . http_build_query($arguments);
        return $url;
    }
}

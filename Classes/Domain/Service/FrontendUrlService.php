<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\FrontendUtility;
use Psr\Http\Message\UriInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Routing\InvalidRouteArgumentsException;
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
     */
    public function getTypolinkUrlFromParameter(int $pageIdentifier, array $arguments = []): string
    {
        $url = ConfigurationUtility::getDomain();
        $url .= $this->getUri($pageIdentifier, $arguments)->getPath();
        if ($arguments !== []) {
            $url .= '?' . $this->getUri($pageIdentifier, $arguments)->getQuery();
        }
        return $url;
    }

    /**
     * @param array $arguments
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function getFrontendUrlFromParameter(array $arguments): string
    {
        $url = ConfigurationUtility::getDomain();
        $url .= '/?' . http_build_query($arguments);
        return $url;
    }

    /**
     * Generates an absolute URL for a page (based on Site Handling)
     *
     * @param int $pageId
     * @param array $arguments
     * @return UriInterface
     * @throws SiteNotFoundException
     * @throws InvalidRouteArgumentsException
     */
    protected function getUri(int $pageId, array $arguments = []): UriInterface
    {
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageId);
        return $site->getRouter()->generateUri($pageId, $arguments);
    }
}

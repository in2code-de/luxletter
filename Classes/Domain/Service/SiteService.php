<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\FrontendUtility;
use In2code\Luxletter\Utility\StringUtility;
use LogicException;
use Throwable;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SiteService
{
    /**
     * Get a site from current page identifier. Works only in frontend context (so not when in CLI and BACKEND context)
     *
     * @param int $pageIdentifier
     * @return Site
     * @throws SiteNotFoundException
     */
    public function getSite(int $pageIdentifier = 0): Site
    {
        if ($pageIdentifier === 0) {
            $pageIdentifier = FrontendUtility::getCurrentPageIdentifier();
        }
        if ($pageIdentifier > 0) {
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            return $siteFinder->getSiteByPageId($pageIdentifier);
        }
        throw new LogicException('No page identifier given. Maybe no frontend context?', 1622813408);
    }

    /**
     * @param int $pageIdentifier
     * @return array
     * @throws SiteNotFoundException
     */
    public function getLanguages(int $pageIdentifier): array
    {
        $site = $this->getSite($pageIdentifier);
        return $site->getLanguages();
    }

    public function getFirstSite(): Site
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $sites = $siteFinder->getAllSites();
        return current($sites);
    }

    /**
     * Get a domain from a site configuration that can be used to prefix (e.g.) links or assets in newsletter bodytext
     *
     * @param Site $site
     * @return string "https://www.domain.org/"
     * @throws MisconfigurationException
     */
    public function getDomainFromSite(Site $site): string
    {
        $this->checkForValidSite($site);
        return $this->getCurrentBaseDomain($site);
    }

    /**
     * @param int $pageIdentifier
     * @param array $arguments
     * @return string
     * @throws MisconfigurationException
     * @throws SiteNotFoundException
     */
    public function getPageUrlFromParameter(int $pageIdentifier, array $arguments = []): string
    {
        $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($pageIdentifier);
        $this->checkForValidSite($site);
        $uri = $site->getRouter()->generateUri($pageIdentifier, $arguments);
        return $uri->__tostring();
    }

    /**
     * Just build an url with a domain and some arguments (so not page needed)
     *
     * @param array $arguments
     * @param Site $site
     * @return string
     * @throws MisconfigurationException
     */
    public function getFrontendUrlFromParameter(array $arguments, Site $site): string
    {
        $this->checkForValidSite($site);
        $siteService = GeneralUtility::makeInstance(SiteService::class);
        $url = $siteService->getDomainFromSite($site);
        $url .= '?' . http_build_query($arguments);
        return $url;
    }

    /**
     * @param Site $site
     * @return void
     * @throws MisconfigurationException
     */
    protected function checkForValidSite(Site $site): void
    {
        $base = $this->getCurrentBaseDomain($site);
        if (StringUtility::startsWith($base, 'http') === false || StringUtility::endsWith($base, '/') === false) {
            throw new MisconfigurationException(
                'Base settings in site configuration is not in format "https://domain.org/"',
                1622832844
            );
        }
    }

    /**
     * Get current base domain with a trailing slash
     *
     * @param Site $site
     * @return string
     */
    protected function getCurrentBaseDomain(Site $site): string
    {
        $base = (string)$site->getBase();
        $base .= (substr($base, -1) === '/' ? '' : '/');
        return $base;
    }
}

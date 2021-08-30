<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\FrontendUtility;
use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SiteService
 */
class SiteService
{
    /**
     * Get a site from current page identifier. Works only in frontend context (so not when in CLI and BACKEND context)
     *
     * @return Site
     * @throws SiteNotFoundException
     */
    public function getSite(): Site
    {
        $pageIdentifier = FrontendUtility::getCurrentPageIdentifier();
        if ($pageIdentifier > 0) {
            /** @var SiteFinder $siteFinder */
            $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
            return $siteFinder->getSiteByPageId($pageIdentifier);
        }
        throw new \LogicException('Not in frontend context? No page identifier given.', 1622813408);
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
     */
    public function getPageUrlFromParameter(int $pageIdentifier, array $arguments = []): string
    {
        /** @var Site $site */
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
        /** @var SiteService $siteService */
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
                'Base settings in site configuration is not in format "https://domain.org/", current base: ' . $base,
                1622832844
            );
        }
    }
    
    /**
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

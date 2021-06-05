<?php
declare(strict_types=1);
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
        } else {
            throw new \LogicException('Not in frontend context? No page identifier given.', 1622813408);
        }
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
        $base = $site->getConfiguration()['base'];
        if (StringUtility::startsWith($base, 'http') === false || StringUtility::endsWith($base, '/') === false) {
            throw new MisconfigurationException(
                'Base settings in site configuration is not in format "https://domain.org/"',
                1622832844
            );
        }
        return $base;
    }
}

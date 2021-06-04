<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Utility\FrontendUtility;
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
}

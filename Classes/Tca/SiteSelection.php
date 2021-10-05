<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Tca;

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SiteSelection
 */
class SiteSelection
{
    /**
     * @param array $configuration
     * @return void
     */
    public function getAll(array &$configuration): void
    {
        foreach ($this->getAllSites() as $site) {
            $configuration['items'][] = [$site->getIdentifier(), $site->getIdentifier()];
        }
    }

    /**
     * @return Site[]
     */
    protected function getAllSites(): array
    {
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        return $siteFinder->getAllSites();
    }
}

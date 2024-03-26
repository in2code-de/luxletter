<?php

declare(strict_types=1);
namespace In2code\Luxletter\Tca;

use In2code\Luxletter\Domain\Service\SiteService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SiteSelection
{
    protected SiteService $siteService;

    public function __construct()
    {
        $this->siteService = GeneralUtility::makeInstance(SiteService::class);
    }

    public function getAll(array &$configuration): void
    {
        foreach ($this->siteService->getAllowedSites() as $site) {
            $configuration['items'][] = [$site->getIdentifier(), $site->getIdentifier()];
        }
    }
}

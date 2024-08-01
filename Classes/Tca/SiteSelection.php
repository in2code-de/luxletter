<?php

declare(strict_types=1);
namespace In2code\Luxletter\Tca;

use In2code\Luxletter\Domain\Service\SiteService;

class SiteSelection
{
    protected SiteService $siteService;

    public function __construct(SiteService $siteService)
    {
        $this->siteService = $siteService;
    }

    public function getAll(array &$configuration): void
    {
        foreach ($this->siteService->getAllowedSites() as $site) {
            $configuration['items'][] = [$site->getIdentifier(), $site->getIdentifier()];
        }
    }
}

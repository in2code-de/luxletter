<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use In2code\Luxletter\Domain\Service\SiteService;
use In2code\Luxletter\Utility\BackendUserUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class ConfigurationRepository extends AbstractRepository
{
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING,
    ];

    public function findAllAuthorized(): QueryResultInterface
    {
        if (BackendUserUtility::isAdministrator()) {
            return $this->findAll();
        }

        $siteService = GeneralUtility::makeInstance(SiteService::class);
        $sites = array_merge(array_keys($siteService->getAllowedSites()), ['']);
        $query = $this->createQuery();
        $query->matching($query->in('site', $sites));
        return $query->execute();
    }
}

<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use In2code\Luxletter\Domain\Model\Dto\Filter;
use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Model\Log;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Domain\Service\SiteService;
use In2code\Luxletter\Utility\BackendUserUtility;
use In2code\Luxletter\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class NewsletterRepository extends AbstractRepository
{
    public function findAllAuthorized(): QueryResultInterface
    {
        $query = $this->createQuery();
        if (BackendUserUtility::isAdministrator() === false) {
            $siteService = GeneralUtility::makeInstance(SiteService::class);
            $query->matching($query->in('configuration.site', array_keys($siteService->getAllowedSites())));
        }
        return $query->execute();
    }

    public function findLatestNewsletter(): ?Newsletter
    {
        $query = $this->createQuery();
        $query->setOrderings(['uid' => QueryInterface::ORDER_DESCENDING]);
        $query->setLimit(1);
        /** @var Newsletter $newsletter */
        $newsletter = $query->execute()->getFirst();
        return $newsletter;
    }

    public function findOneNotQueued(): ?Newsletter
    {
        $query = $this->createQuery();
        $query->matching($query->equals('queued', false));
        $query->setOrderings(['uid' => QueryInterface::ORDER_ASCENDING]);
        return $query->execute()->getFirst();
    }

    public function removeNewsletterAndQueues(Newsletter $newsletter): void
    {
        $connection = DatabaseUtility::getConnectionForTable(Queue::TABLE_NAME);
        /** @noinspection SqlDialectInspection */
        $connection->executeQuery('delete from ' . Queue::TABLE_NAME . ' where newsletter=' . $newsletter->getUid());
        $this->remove($newsletter);
    }

    public function findAllGroupedByCategories(Filter $filter): array
    {
        $newsletters = $this->findAllByFilter($filter);
        $newslettersGrouped = [];
        /** @var Newsletter $newsletter */
        foreach ($newsletters as $newsletter) {
            $categoryKey = $this->getDefaultCategoryLabel();
            if ($newsletter->getCategory() !== null) {
                $categoryKey = $newsletter->getCategory()->getTitle();
            }
            $newslettersGrouped[$categoryKey][] = $newsletter;
        }
        uksort($newslettersGrouped, [$this, 'sortByKeyAndIgnoreDefaultLabelCallback']);
        return $newslettersGrouped;
    }

    protected function findAllByFilter(Filter $filter): ?QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = [];
        if ($filter->isSet()) {
            if ($filter->getSearchterm() !== '') {
                $logicalOr = [];
                foreach ($filter->getSearchterms() as $searchterm) {
                    $logicalOr[] = $query->like('title', '%' . $searchterm . '%');
                    $logicalOr[] = $query->like('description', '%' . $searchterm . '%');
                    $logicalOr[] = $query->like('subject', '%' . $searchterm . '%');
                }
                $logicalAnd[] = $query->logicalOr(...$logicalOr);
            }
            if ($filter->getCategory() !== null) {
                $logicalAnd[] = $query->equals('category', $filter->getCategory());
            }
            if ($filter->getTime() > 0) {
                $logicalAnd[] = $query->greaterThanOrEqual('crdate', $filter->getTimeDateStart());
            }
            if ($filter->isConfigurationSet()) {
                $logicalAnd[] = $query->equals('configuration', $filter->getConfiguration());
            }
        }
        if (BackendUserUtility::isAdministrator() === false) {
            $siteService = GeneralUtility::makeInstance(SiteService::class);
            $logicalAnd[] = $query->in('configuration.site', array_keys($siteService->getAllowedSites()));
        }
        if ($logicalAnd !== []) {
            $query->matching($query->logicalAnd(...$logicalAnd));
        }
        return $query->execute();
    }

    /**
     * Remove (really remove) all data from all luxletter tables
     *
     * @return void
     */
    public function truncateAll(): void
    {
        $tables = [
            Newsletter::TABLE_NAME,
            Link::TABLE_NAME,
            Log::TABLE_NAME,
            Queue::TABLE_NAME,
        ];
        foreach ($tables as $table) {
            DatabaseUtility::getConnectionForTable($table)->truncate($table);
        }
    }

    /**
     * Sort by key but ignore the default label. This label should always be ordered at the very last entry.
     *
     * @param string $a
     * @param string $b
     * @return int
     */
    protected function sortByKeyAndIgnoreDefaultLabelCallback(string $a, string $b): int
    {
        $result = strcasecmp($a, $b);
        if ($result !== 0) {
            if ($a === $this->getDefaultCategoryLabel() || $b === $this->getDefaultCategoryLabel()) {
                return $a === $this->getDefaultCategoryLabel() ? 1 : -1;
            }
        }
        return $result;
    }

    protected function getDefaultCategoryLabel(): string
    {
        return LocalizationUtility::translate(
            'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:category.empty.title'
        );
    }
}

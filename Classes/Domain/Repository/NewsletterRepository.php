<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Luxletter\Domain\Model\Dto\Filter;
use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Model\Log;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Utility\DatabaseUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class NewsletterRepository
 */
class NewsletterRepository extends AbstractRepository
{
    /**
     * @return Newsletter|null
     */
    public function findLatestNewsletter(): ?Newsletter
    {
        $query = $this->createQuery();
        $query->setOrderings(['uid', QueryInterface::ORDER_DESCENDING]);
        $query->setLimit(1);
        /** @var Newsletter $newsletter */
        /** @noinspection PhpUnnecessaryLocalVariableInspection */
        $newsletter = $query->execute()->getFirst();
        return $newsletter;
    }

    /**
     * @param Newsletter $newsletter
     * @return void
     * @throws DBALException
     * @throws IllegalObjectTypeException
     */
    public function removeNewsletterAndQueues(Newsletter $newsletter): void
    {
        $connection = DatabaseUtility::getConnectionForTable(Queue::TABLE_NAME);
        /** @noinspection SqlDialectInspection */
        $connection->executeQuery('delete from ' . Queue::TABLE_NAME . ' where newsletter=' . $newsletter->getUid());
        $this->remove($newsletter);
    }

    /**
     * @param Filter $filter
     * @return array
     * @throws InvalidQueryException
     */
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

    /**
     * @param Filter $filter
     * @return QueryResultInterface|null
     * @throws InvalidQueryException
     */
    protected function findAllByFilter(Filter $filter): ?QueryResultInterface
    {
        $query = $this->createQuery();
        if ($filter->isSet()) {
            $logicalAnd = [$query->greaterThan('uid', 0)];
            if ($filter->getSearchterm() !== '') {
                $logicalOr = [];
                foreach ($filter->getSearchterms() as $searchterm) {
                    $logicalOr[] = $query->like('title', '%' . $searchterm . '%');
                    $logicalOr[] = $query->like('description', '%' . $searchterm . '%');
                    $logicalOr[] = $query->like('subject', '%' . $searchterm . '%');
                }
                $logicalAnd[] = $query->logicalOr($logicalOr);
            }
            if ($filter->getCategory() !== null) {
                $logicalAnd[] = $query->equals('category', $filter->getCategory());
            }
            if ($filter->getTime() > 0) {
                $logicalAnd[] = $query->greaterThanOrEqual('crdate', $filter->getTimeDateStart());
            }
            $query->matching($query->logicalAnd($logicalAnd));
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

    /**
     * @return string
     */
    protected function getDefaultCategoryLabel(): string
    {
        return LocalizationUtility::translate(
            'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:category.empty.title'
        );
    }
}

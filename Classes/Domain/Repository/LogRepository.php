<?php

/** @noinspection SqlDialectInspection */
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Luxletter\Domain\Model\Configuration;
use In2code\Luxletter\Domain\Model\Dto\Filter;
use In2code\Luxletter\Domain\Model\Log;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Utility\ArrayUtility;
use In2code\Luxletter\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class LogRepository extends AbstractRepository
{
    /**
     * @param Filter $filter
     * @return int
     * @throws ExceptionDbal
     */
    public function getNumberOfReceivers(Filter $filter): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        $sql = 'select count(distinct user) '
            . ' from ' . Log::TABLE_NAME . ' l'
            . ' left join ' . Newsletter::TABLE_NAME . ' nl on nl.uid=l.newsletter'
            . ' left join ' . Configuration::TABLE_NAME . ' c on nl.configuration=c.uid'
            . ' where l.deleted=0 and l.status=' . Log::STATUS_DISPATCH
            . ' and c.site in ("' . implode('","', $filter->getSitesForFilter()) . '")'
            . ' and nl.crdate>' . $filter->getTimeDateStart()->getTimestamp()
            . ' limit 1';
        return (int)$connection->executeQuery($sql)->fetchOne();
    }

    /**
     * Example result value:
     *  0 => [
     *      'count' => 2,
     *      'properties' => '{"target":"https:\/\/de.wikipedia.org\/wiki\/Haushund"}',
     *      'newsletter' => Newsletter::class
     *      'target' => 'https://de.wikipedia.org/wiki/Haushund'
     *  ]
     *
     * @param Filter $filter
     * @return array
     * @throws ExceptionDbal
     */
    public function getGroupedLinksByHref(Filter $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        $sql = 'select count(*) as count, l.properties, l.newsletter, MAX(l.uid) uid'
            . ' from ' . Log::TABLE_NAME . ' l'
            . ' left join ' . Newsletter::TABLE_NAME . ' nl on nl.uid=l.newsletter'
            . ' left join ' . Configuration::TABLE_NAME . ' c on nl.configuration=c.uid'
            . ' where l.deleted=0 and l.status=' . Log::STATUS_LINKOPENING
            . ' and c.site in ("' . implode('","', $filter->getSitesForFilter()) . '")'
            . ' and nl.crdate>' . $filter->getTimeDateStart()->getTimestamp()
            . ' group by l.properties, l.newsletter'
            . ' order by count desc'
            . ' limit ' . $filter->getLimit();
        $results = $connection->executeQuery($sql)->fetchAllAssociative();
        $nlRepository = GeneralUtility::makeInstance(NewsletterRepository::class);
        foreach ($results as &$result) {
            $result['target'] = json_decode($result['properties'], true)['target'];
            $result['newsletter'] = $nlRepository->findByUid($result['newsletter']);
        }
        return $results;
    }

    /**
     * @param Filter $filter
     * @return int
     * @throws ExceptionDbal
     */
    public function getOverallOpenings(Filter $filter): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        $sql = 'select count(distinct newsletter, user)'
            . ' from ' . Log::TABLE_NAME . ' l'
            . ' left join ' . Newsletter::TABLE_NAME . ' nl on nl.uid=l.newsletter'
            . ' left join ' . Configuration::TABLE_NAME . ' c on nl.configuration=c.uid'
            . ' where l.deleted = 0'
            . ' and l.status IN (' . Log::STATUS_NEWSLETTEROPENING . ',' . Log::STATUS_LINKOPENING . ')'
            . ' and c.site in ("' . implode('","', $filter->getSitesForFilter()) . '")'
            . ' and nl.crdate>' . $filter->getTimeDateStart()->getTimestamp();
        return (int)$connection->executeQuery($sql)->fetchOne();
    }

    /**
     * @param Filter $filter
     * @return int
     * @throws ExceptionDbal
     */
    public function getOpeningsByClickers(Filter $filter): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        $sql = 'select count(distinct newsletter, user)'
            . ' from ' . Log::TABLE_NAME . ' l'
            . ' left join ' . Newsletter::TABLE_NAME . ' nl on nl.uid=l.newsletter'
            . ' left join ' . Configuration::TABLE_NAME . ' c on nl.configuration=c.uid'
            . ' where l.deleted = 0 and l.status=' . Log::STATUS_LINKOPENING
            . ' and c.site in ("' . implode('","', $filter->getSitesForFilter()) . '")'
            . ' and nl.crdate>' . $filter->getTimeDateStart()->getTimestamp();
        return (int)$connection->executeQuery($sql)->fetchOne();
    }

    /**
     * @param Filter $filter
     * @return int
     * @throws ExceptionDbal
     */
    public function getOverallClicks(Filter $filter): int
    {
        return $this->getOverallAmountByLogStatus([Log::STATUS_LINKOPENING], $filter);
    }

    /**
     * @param Filter $filter
     * @return int
     * @throws ExceptionDbal
     */
    public function getOverallUnsubscribes(Filter $filter): int
    {
        return $this->getOverallAmountByLogStatus([Log::STATUS_UNSUBSCRIBE], $filter);
    }

    /**
     * @param Filter $filter
     * @return int
     * @throws ExceptionDbal
     */
    public function getOverallMailsSent(Filter $filter): int
    {
        return $this->getOverallAmountByLogStatus([Log::STATUS_DISPATCH], $filter);
    }

    /**
     * @param array $status
     * @param Filter $filter
     * @return int
     * @throws ExceptionDbal
     */
    protected function getOverallAmountByLogStatus(array $status, Filter $filter): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        $sql = 'select count(l.uid)'
            . ' from ' . Log::TABLE_NAME . ' l'
            . ' left join ' . Newsletter::TABLE_NAME . ' nl on nl.uid=l.newsletter'
            . ' left join ' . Configuration::TABLE_NAME . ' c on nl.configuration=c.uid'
            . ' where l.deleted = 0 and l.status in (' . ArrayUtility::convertArrayToIntegerList($status) . ')'
            . ' and c.site in ("' . implode('","', $filter->getSitesForFilter()) . '")'
            . ' and nl.crdate>' . $filter->getTimeDateStart()->getTimestamp();
        return (int)$connection->executeQuery($sql)->fetchOne();
    }

    /**
     * @param Filter $filter
     * @return float
     * @throws ExceptionDbal
     */
    public function getOverallOpenRate(Filter $filter): float
    {
        $overallSent = $this->getOverallMailsSent($filter);
        $overallOpenings = $this->getOverallOpenings($filter);
        if ($overallSent > 0) {
            $result = $overallOpenings / $overallSent;
            if ($result > 1) {
                return 1.0;
            }
            return $result;
        }
        return 0.0;
    }

    /**
     * @param Filter $filter
     * @return float
     * @throws ExceptionDbal
     */
    public function getOverallClickRate(Filter $filter): float
    {
        $overallOpenings = $this->getOverallOpenings($filter);
        $openingsByClickers = $this->getOpeningsByClickers($filter);
        if ($overallOpenings > 0) {
            $result = $openingsByClickers / $overallOpenings;
            if ($result > 1) {
                return 1.0;
            }
            return $result;
        }
        return 0.0;
    }

    /**
     * @param Filter $filter
     * @return float
     * @throws ExceptionDbal
     */
    public function getOverallUnsubscribeRate(Filter $filter): float
    {
        $overallUnsubscribes = $this->getOverallUnsubscribes($filter);
        $overallMailsSent = $this->getOverallMailsSent($filter);
        if ($overallMailsSent > 0) {
            $result = $overallUnsubscribes / $overallMailsSent;
            if ($result > 1) {
                return 1.0;
            }
            return $result;
        }
        return 0.0;
    }

    /**
     * @param Filter $filter
     * @return int
     * @throws ExceptionDbal
     */
    public function getOverallNonOpenings(Filter $filter): int
    {
        $mailsSent = $this->getOverallMailsSent($filter);
        $openings = $this->getOverallOpenings($filter);
        $result = $mailsSent - $openings;
        if ($result > 0) {
            return $result;
        }
        return 0;
    }

    /**
     * @param Filter $filter
     * @return int
     * @throws ExceptionDbal
     */
    public function getOverallNonClicks(Filter $filter): int
    {
        $openings = $this->getOverallOpenings($filter);
        $openingsByClickers = $this->getOpeningsByClickers($filter);
        $result = $openings - $openingsByClickers;
        if ($result > 0) {
            return $result;
        }
        return 0;
    }

    /**
     * @param Filter $filter
     * @return int
     * @throws ExceptionDbal
     */
    public function getOverallSubscribes(Filter $filter): int
    {
        $mailsSent = $this->getOverallMailsSent($filter);
        $unsubscribes = $this->getOverallUnsubscribes($filter);
        $result = $mailsSent - $unsubscribes;
        if ($result > 0) {
            return $result;
        }
        return 0;
    }

    /**
     * @param Newsletter $newsletter
     * @param User $user
     * @param int $status
     * @return bool
     * @throws ExceptionDbal
     */
    public function isLogRecordExisting(Newsletter $newsletter, User $user, int $status): bool
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Log::TABLE_NAME);
        $uid = (int)$queryBuilder
            ->select('uid')
            ->from(Log::TABLE_NAME)
            ->where('newsletter=' . $newsletter->getUid() . ' and user=' . $user->getUid() . ' and status=' . $status)
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();
        return $uid > 0;
    }

    /**
     * @param Newsletter $newsletter
     * @param int[] $status
     * @param bool $distinctMails
     * @return array
     * @throws ExceptionDbal
     */
    public function findByNewsletterAndStatus(Newsletter $newsletter, array $status, bool $distinctMails = true): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        $sqlSelectColumns = '*';
        if ($distinctMails === true) {
            $sqlSelectColumns = 'distinct newsletter, user';
        }
        return $connection->executeQuery(
            'select ' . $sqlSelectColumns . ' from ' . Log::TABLE_NAME .
            ' where deleted=0 and status in (' . implode(',', $status) . ') and newsletter=' . $newsletter->getUid()
        )->fetchAllAssociative();
    }

    /**
     * @param User $user
     * @param array $statusWhitelist only want logs with this status (overrules any values from $statusBlacklist)
     * @param array $statusBlacklist ignore logs with this status
     * @return array
     * @throws ExceptionDbal
     */
    public function findRawByUser(User $user, array $statusWhitelist = [], array $statusBlacklist = []): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        $sql = 'select * from ' . Log::TABLE_NAME . ' where deleted=0 and user=' . $user->getUid();
        if ($statusWhitelist !== []) {
            $sql .= ' and status in (' . implode(',', $statusWhitelist) . ')';
        } elseif ($statusBlacklist !== []) {
            $sql .= ' and status not in (' . implode(',', $statusBlacklist) . ')';
        }
        $sql .= ' order by crdate desc';
        return (array)$connection->executeQuery($sql)->fetchAllAssociative();
    }

    /**
     * @param ?User $user
     * @return ?QueryResultInterface
     */
    public function findByUser(?User $user): ?QueryResultInterface
    {
        if ($user === null) {
            return null;
        }
        $query = $this->createQuery();
        $query->matching($query->equals('user', $user));
        $query->setLimit(100);
        return $query->execute();
    }
}

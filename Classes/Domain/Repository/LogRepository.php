<?php
/** @noinspection SqlDialectInspection */
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Luxletter\Domain\Model\Log;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class LogRepository
 */
class LogRepository extends AbstractRepository
{
    /**
     * @return int
     * @throws Exception
     * @throws ExceptionDbal
     */
    public function getNumberOfReceivers(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        return (int)$connection->executeQuery(
            'select count(DISTINCT user) from ' . Log::TABLE_NAME .
            ' where deleted=0 and status=' . Log::STATUS_DISPATCH . ';'
        )->fetchOne();
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
     * @param int $limit
     * @return array
     * @throws DBALException
     */
    public function getGroupedLinksByHref(int $limit = 8): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        $results = (array)$connection->executeQuery(
            'select count(*) as count, properties, newsletter from ' . Log::TABLE_NAME .
            ' where deleted=0 and status=' . Log::STATUS_LINKOPENING .
            ' group by properties,newsletter order by count desc limit ' . $limit
        )->fetchAll();
        $nlRepository = GeneralUtility::makeInstance(NewsletterRepository::class);
        foreach ($results as &$result) {
            $result['target'] = json_decode($result['properties'], true)['target'];
            $result['newsletter'] = $nlRepository->findByUid($result['newsletter']);
        }
        return $results;
    }

    /**
     * @return int
     * @throws Exception
     * @throws ExceptionDbal
     */
    public function getOverallOpenings(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        return (int)$connection->executeQuery(
            'select count(uid) from ' . Log::TABLE_NAME .
            ' where deleted = 0 and status=' . Log::STATUS_NEWSLETTEROPENING . ';'
        )->fetchOne();
    }

    /**
     * @return int
     * @throws Exception
     * @throws ExceptionDbal
     */
    public function getOverallClicks(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        return (int)$connection->executeQuery(
            'select count(uid) from ' . Log::TABLE_NAME .
            ' where deleted = 0 and status=' . Log::STATUS_LINKOPENING . ';'
        )->fetchOne();
    }

    /**
     * @return int
     * @throws Exception
     * @throws ExceptionDbal
     */
    public function getOverallUnsubscribes(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        return (int)$connection->executeQuery(
            'select count(uid) from ' . Log::TABLE_NAME .
            ' where deleted = 0 and status=' . Log::STATUS_UNSUBSCRIBE . ';'
        )->fetchOne();
    }

    /**
     * @return int
     * @throws Exception
     * @throws ExceptionDbal
     */
    public function getOverallMailsSent(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        return (int)$connection->executeQuery(
            'select count(uid) from ' . Log::TABLE_NAME .
            ' where deleted = 0 and status=' . Log::STATUS_DISPATCH . ';'
        )->fetchOne();
    }

    /**
     * @return float
     * @throws DBALException
     * @throws Exception
     * @throws ExceptionDbal
     */
    public function getOverallOpenRate(): float
    {
        $overallSent = $this->getOverallMailsSent();
        $overallOpenings = $this->getOverallOpenings();
        if ($overallSent > 0) {
            return $overallOpenings / $overallSent;
        }
        return 0.0;
    }

    /**
     * @return float
     * @throws DBALException
     * @throws Exception
     * @throws ExceptionDbal
     */
    public function getOverallClickRate(): float
    {
        $overallOpenings = $this->getOverallOpenings();
        $overallClicks = $this->getOverallClicks();
        if ($overallOpenings > 0) {
            return $overallClicks / $overallOpenings;
        }
        return 0.0;
    }

    /**
     * @return float
     * @throws DBALException
     * @throws Exception
     * @throws ExceptionDbal
     */
    public function getOverallUnsubscribeRate(): float
    {
        $overallOpenings = $this->getOverallOpenings();
        $overallUnsubscribes = $this->getOverallUnsubscribes();
        if ($overallOpenings > 0) {
            return $overallUnsubscribes / $overallOpenings;
        }
        return 0.0;
    }

    /**
     * @param Newsletter $newsletter
     * @param User $user
     * @param int $status
     * @return bool
     * @throws Exception
     */
    public function isLogRecordExisting(Newsletter $newsletter, User $user, int $status): bool
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Log::TABLE_NAME);
        $uid = (int)$queryBuilder
            ->select('uid')
            ->from(Log::TABLE_NAME)
            ->where('newsletter=' . $newsletter->getUid() . ' and user=' . $user->getUid() . ' and status=' . $status)
            ->setMaxResults(1)
            ->execute()
            ->fetchOne();
        return $uid > 0;
    }

    /**
     * @param Newsletter $newsletter
     * @param int $status
     * @return array
     * @throws DBALException
     * @throws Exception
     */
    public function findByNewsletterAndStatus(Newsletter $newsletter, int $status): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        return (array)$connection->executeQuery(
            'select * from ' . Log::TABLE_NAME .
            ' where deleted=0 and status=' . $status . ' and newsletter=' . $newsletter->getUid()
        )->fetchAllAssociative();
    }

    /**
     * @param User $user
     * @param array $statusWhitelist only want logs with this status (overrules any values from $statusBlacklist)
     * @param array $statusBlacklist ignore logs with this status
     * @return array
     * @throws DBALException
     * @throws Exception
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
     * @param User $user
     * @return QueryResultInterface
     */
    public function findByUser(User $user): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('user', $user));
        return $query->execute();
    }
}

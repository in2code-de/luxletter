<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class QueueRepository extends AbstractRepository
{
    const FAILURE_COUNT = 3;

    /**
     * @param int $limit
     * @param int $newsletterIdentifier
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findDispatchableInQueue(int $limit, int $newsletterIdentifier): QueryResultInterface
    {
        $query = $this->createQuery();
        $and = [
            $query->lessThan('datetime', time()),
            $query->equals('sent', false),
            $query->lessThan('failures', self::FAILURE_COUNT),
            $query->equals('newsletter.disabled', false),
            $query->greaterThan('newsletter.configuration', 0),
            $query->logicalNot($query->equals('newsletter.layout', '')),
            $query->equals('user.deleted', false),
            $query->equals('user.disable', false),
        ];
        if ($newsletterIdentifier > 0) {
            $and[] = $query->equals('newsletter.uid', $newsletterIdentifier);
        }
        $query->matching($query->logicalAnd(...$and));
        $query->setLimit($limit);
        $query->setOrderings(['tstamp' => QueryInterface::ORDER_ASCENDING]);
        return $query->execute();
    }

    public function findAllByNewsletterAndDispatchedStatus(
        Newsletter $newsletter,
        bool $dispatched = false
    ): QueryResultInterface {
        $query = $this->createQuery();
        $and = [
            $query->equals('sent', $dispatched),
            $query->equals('newsletter', $newsletter),
        ];
        $query->matching($query->logicalAnd(...$and));
        return $query->execute();
    }

    /**
     * @param Newsletter $newsletter
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findAllByNewsletterAndFailedStatus(Newsletter $newsletter): QueryResultInterface
    {
        $query = $this->createQuery();
        $and = [
            $query->greaterThanOrEqual('failures', self::FAILURE_COUNT),
            $query->equals('newsletter', $newsletter),
        ];
        $query->matching($query->logicalAnd(...$and));
        return $query->execute();
    }

    public function findAllByNewsletter(Newsletter $newsletter): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('newsletter', $newsletter));
        return $query->execute();
    }

    /**
     * Check if there is already a queue entry to this user with the same newsletter (don't care about sent status)
     *
     * @param User $user
     * @param Newsletter $newsletter
     * @return bool
     * @throws ExceptionDbal
     */
    public function isUserAndNewsletterAlreadyAddedToQueue(User $user, Newsletter $newsletter): bool
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Queue::TABLE_NAME);
        return (int)$queryBuilder
            ->select('uid')
            ->from(Queue::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq(
                    'newsletter',
                    $queryBuilder->createNamedParameter($newsletter->getUid(), Connection::PARAM_INT)
                ),
                $queryBuilder->expr()->eq(
                    'user',
                    $queryBuilder->createNamedParameter($user->getUid(), Connection::PARAM_INT)
                )
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne() > 0;
    }

    public function truncate(): void
    {
        $tables = [
            Queue::TABLE_NAME,
        ];
        foreach ($tables as $table) {
            DatabaseUtility::getConnectionForTable($table)->truncate($table);
        }
    }
}

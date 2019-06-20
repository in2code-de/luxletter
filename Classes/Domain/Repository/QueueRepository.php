<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Utility\DatabaseUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class QueueRepository
 */
class QueueRepository extends AbstractRepository
{

    /**
     * @param int $limit
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findDispatchableInQueue(int $limit): QueryResultInterface
    {
        $query = $this->createQuery();
        $and = [
            $query->lessThan('datetime', time()),
            $query->equals('newsletter.disabled', false)
        ];
        $query->matching($query->logicalAnd($and));
        $query->setLimit($limit);
        return $query->execute();
    }

    /**
     * Delete a record completely from database
     *
     * @param Queue $queue
     * @return void
     */
    public function delete(Queue $queue)
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Queue::TABLE_NAME);
        $queryBuilder->delete(Queue::TABLE_NAME)->where('uid=' . $queue->getUid())->execute();
    }
}

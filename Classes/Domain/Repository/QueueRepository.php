<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

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
            $query->equals('sent', false),
            $query->equals('newsletter.disabled', false)
        ];
        $query->matching($query->logicalAnd($and));
        $query->setLimit($limit);
        return $query->execute();
    }
}

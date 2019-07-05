<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use In2code\Luxletter\Domain\Model\Newsletter;
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

    /**
     * @param Newsletter $newsletter
     * @param bool $dispatched
     * @return QueryResultInterface
     */
    public function findAllByNewsletterAndDispatchedStatus(Newsletter $newsletter, bool $dispatched = false): QueryResultInterface
    {
        $query = $this->createQuery();
        $and = [
            $query->equals('sent', $dispatched),
            $query->equals('newsletter', $newsletter)
        ];
        $query->matching($query->logicalAnd($and));
        return $query->execute();
    }

    /**
     * @param Newsletter $newsletter
     * @return QueryResultInterface
     */
    public function findAllByNewsletter(Newsletter $newsletter): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('newsletter', $newsletter));
        return $query->execute();
    }
}

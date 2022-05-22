<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Events;

use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

final class ProgressQueueEvent
{
    /**
     * @var QueryResultInterface
     */
    protected $queues;

    /**
     * @var int
     */
    protected $newsletterIdentifier;

    /**
     * Constructor
     *
     * @param QueryResultInterface $queues
     * @param int $newsletterIdentifier
     */
    public function __construct(QueryResultInterface $queues, int $newsletterIdentifier)
    {
        $this->queues = $queues;
        $this->newsletterIdentifier = $newsletterIdentifier;
    }

    /**
     * @return QueryResultInterface
     */
    public function getQueues(): QueryResultInterface
    {
        return $this->queues;
    }

    /**
     * @return int
     */
    public function getNewsletterIdentifier(): int
    {
        return $this->newsletterIdentifier;
    }
}

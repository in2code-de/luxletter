<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Model\Newsletter;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

final class QueueServiceAddMailReceiversToQueueEvent
{
    /**
     * @var QueryResultInterface
     */
    protected $users;

    /**
     * @var Newsletter
     */
    protected $newsletter;

    /**
     * @var int
     */
    protected $language;

    /**
     * @param QueryResultInterface $users
     * @param Newsletter $newsletter
     * @param int $language
     */
    public function __construct(QueryResultInterface $users, Newsletter $newsletter, int $language)
    {
        $this->users = $users;
        $this->newsletter = $newsletter;
        $this->language = $language;
    }

    /**
     * @return QueryResultInterface
     */
    public function getUsers(): QueryResultInterface
    {
        return $this->users;
    }

    /**
     * @param QueryResultInterface $users
     * @return QueueServiceAddMailReceiversToQueueEvent
     */
    public function setUsers(QueryResultInterface $users): QueueServiceAddMailReceiversToQueueEvent
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @return Newsletter
     */
    public function getNewsletter(): Newsletter
    {
        return $this->newsletter;
    }

    /**
     * @return int
     */
    public function getLanguage(): int
    {
        return $this->language;
    }
}

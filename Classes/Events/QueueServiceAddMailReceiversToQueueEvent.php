<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Model\Newsletter;

final class QueueServiceAddMailReceiversToQueueEvent
{
    /**
     * @var iterable
     */
    protected $users = [];

    /**
     * @var Newsletter
     */
    protected $newsletter;

    /**
     * @var int
     */
    protected $language;

    /**
     * @param iterable $users
     * @param Newsletter $newsletter
     * @param int $language
     */
    public function __construct(iterable $users, Newsletter $newsletter, int $language)
    {
        $this->users = $users;
        $this->newsletter = $newsletter;
        $this->language = $language;
    }

    /**
     * @return iterable
     */
    public function getUsers(): iterable
    {
        return $this->users;
    }

    /**
     * @param iterable $users
     * @return QueueServiceAddMailReceiversToQueueEvent
     */
    public function setUsers(iterable $users): QueueServiceAddMailReceiversToQueueEvent
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

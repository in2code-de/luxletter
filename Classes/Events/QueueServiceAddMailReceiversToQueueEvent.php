<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Model\Newsletter;

final class QueueServiceAddMailReceiversToQueueEvent
{
    /**
     * @var array
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
     * @param array $users
     * @param Newsletter $newsletter
     * @param int $language
     */
    public function __construct(array $users, Newsletter $newsletter, int $language)
    {
        $this->users = $users;
        $this->newsletter = $newsletter;
        $this->language = $language;
    }

    /**
     * @return array
     */
    public function getUsers(): array
    {
        return $this->users;
    }

    /**
     * @param array $users
     * @return QueueServiceAddMailReceiversToQueueEvent
     */
    public function setUsers(array $users): QueueServiceAddMailReceiversToQueueEvent
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

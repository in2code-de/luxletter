<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Domain\Model\User;

final class QueueServiceAddUserToQueueEvent
{
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Newsletter
     */
    protected $newsletter;

    /**
     * @param Queue $queue
     * @param User $user
     * @param Newsletter $newsletter
     */
    public function __construct(Queue $queue, User $user, Newsletter $newsletter)
    {
        $this->queue = $queue;
        $this->user = $user;
        $this->newsletter = $newsletter;
    }

    /**
     * @return Queue
     */
    public function getQueue(): Queue
    {
        return $this->queue;
    }

    /**
     * @param Queue $queue
     * @return QueueServiceAddUserToQueueEvent
     */
    public function setQueue(Queue $queue): QueueServiceAddUserToQueueEvent
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return QueueServiceAddUserToQueueEvent
     */
    public function setUser(User $user): QueueServiceAddUserToQueueEvent
    {
        $this->user = $user;
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
     * @param Newsletter $newsletter
     * @return QueueServiceAddUserToQueueEvent
     */
    public function setNewsletter(Newsletter $newsletter): QueueServiceAddUserToQueueEvent
    {
        $this->newsletter = $newsletter;
        return $this;
    }
}

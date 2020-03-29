<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\QueueRepository;
use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Signal\SignalTrait;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class QueueService
 * to add receivers to queue
 */
class QueueService
{
    use SignalTrait;

    /**
     * Add mail receivers to the queue based on a given newsletter with a relation to a frontenduser group
     *
     * @param Newsletter $newsletter
     * @return void
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws Exception
     * @throws IllegalObjectTypeException
     */
    public function addMailReceiversToQueue(Newsletter $newsletter): void
    {
        $userRepository = ObjectUtility::getObjectManager()->get(UserRepository::class);
        $users = $userRepository->getUsersFromGroup($newsletter->getReceiver()->getUid());
        $this->signalDispatch(__CLASS__, __FUNCTION__, [$users, $newsletter]);
        /** @var User $user */
        foreach ($users as $user) {
            $this->addUserToQueue($newsletter, $user);
        }
    }

    /**
     * Add a new queue entry with a user and a relation to a newsletter but only if
     *  - user has a valid email
     *  - there is no entry yet
     *
     * @param Newsletter $newsletter
     * @param User $user
     * @return void
     * @throws Exception
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws IllegalObjectTypeException
     */
    protected function addUserToQueue(Newsletter $newsletter, User $user): void
    {
        $queueRepository = ObjectUtility::getObjectManager()->get(QueueRepository::class);
        if ($user->isValidEmail()
            && $queueRepository->isUserAndNewsletterAlreadyAddedToQueue($user, $newsletter) === false) {
            $queue = ObjectUtility::getObjectManager()->get(Queue::class);
            $queue
                ->setEmail($user->getEmail())
                ->setUser($user)
                ->setNewsletter($newsletter)
                ->setDatetime($newsletter->getDatetime());
            $this->signalDispatch(__CLASS__, __FUNCTION__, [$queue, $user, $newsletter]);
            $queueRepository->add($queue);
        }
    }
}

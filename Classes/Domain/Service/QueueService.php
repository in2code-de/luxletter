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
 */
class QueueService
{
    use SignalTrait;

    /**
     * Add a lot of queue entries from a given newsletter
     *
     * @param Newsletter $newsletter
     * @return void
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws Exception
     */
    public function addMailReceiversToQueue(Newsletter $newsletter): void
    {
        $queueRepository = ObjectUtility::getObjectManager()->get(QueueRepository::class);
        $userRepository = ObjectUtility::getObjectManager()->get(UserRepository::class);
        $users = $userRepository->getUsersFromGroup($newsletter->getReceiver()->getUid());
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'users', [$users, $newsletter]);
        /** @var User $user */
        foreach ($users as $user) {
            if ($user->isValidEmail()) {
                $queue = ObjectUtility::getObjectManager()->get(Queue::class);
                $queue
                    ->setEmail($user->getEmail())
                    ->setUser($user)
                    ->setNewsletter($newsletter)
                    ->setDatetime($newsletter->getDatetime());
                $this->signalDispatch(__CLASS__, __FUNCTION__ . 'user', [$queue, $user, $newsletter]);
                $queueRepository->add($queue);
            }
        }
    }
}

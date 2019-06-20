<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\QueueRepository;
use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * Class QueueService
 */
class QueueService
{

    /**
     * Add a lot of queue entries from a given newsletter
     *
     * @param Newsletter $newsletter
     * @return string
     * @throws IllegalObjectTypeException
     */
    public function addMailReceiversToQueue(Newsletter $newsletter)
    {
        $queueRepository = ObjectUtility::getObjectManager()->get(QueueRepository::class);
        $userRepository = ObjectUtility::getObjectManager()->get(UserRepository::class);
        $users = $userRepository->getUsersFromGroup($newsletter->getReceiver()->getUid());
        /** @var User $user */
        foreach ($users as $user) {
            $queue = ObjectUtility::getObjectManager()->get(Queue::class);
            $queue
                ->setEmail($user->getEmail())
                ->setUser($user)
                ->setNewsletter($newsletter)
                ->setDatetime($newsletter->getDatetime());
            $queueRepository->add($queue);
        }
    }
}

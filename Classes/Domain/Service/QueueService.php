<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Service;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Domain\Repository\QueueRepository;
use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Events\QueueServiceAddMailReceiversToQueueEvent;
use In2code\Luxletter\Events\QueueServiceAddUserToQueueEvent;
use In2code\Luxletter\Exception\RecordInDatabaseNotFoundException;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * Class QueueService
 * to add receivers to queue
 */
class QueueService
{
    /**
     * @var UserRepository
     */
    protected $userRepository = null;

    /**
     * @var NewsletterRepository
     */
    protected $newsletterRepository = null;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param UserRepository $userRepository
     * @param NewsletterRepository $newsletterRepository
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(
        UserRepository $userRepository,
        NewsletterRepository $newsletterRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->userRepository = $userRepository;
        $this->newsletterRepository = $newsletterRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Add mail receivers to the queue based on a given newsletter with a relation to a frontenduser group
     *
     * @param Newsletter $newsletter
     * @param int $language
     * @return int
     * @throws ExceptionDbalDriver
     * @throws IllegalObjectTypeException
     */
    public function addMailReceiversToQueue(Newsletter $newsletter, int $language): int
    {
        $users = $this->userRepository->getUsersFromGroup($newsletter->getReceiver()->getUid(), $language);
        /** @var QueueServiceAddMailReceiversToQueueEvent $event */
        $event = $this->eventDispatcher->dispatch(GeneralUtility::makeInstance(
            QueueServiceAddMailReceiversToQueueEvent::class,
            $users,
            $newsletter,
            $language
        ));
        /** @var User $user */
        foreach ($event->getUsers() as $user) {
            $this->addUserToQueue($newsletter, $user);
        }
        return $users->count();
    }

    /**
     * This function is part of the API and can be used from registration extensions to add users to a queue after
     * a registration (e.g.)
     *
     * @param int $userIdentifier fe_users.uid
     * @return void
     * @throws RecordInDatabaseNotFoundException
     * @throws IllegalObjectTypeException
     * @throws ExceptionDbalDriver
     *
     * @api (can be used from third party extensions)
     * @noinspection PhpUnused
     */
    public function addUserWithLatestNewsletterToQueue(int $userIdentifier): void
    {
        /** @var User $user */
        $user = $this->userRepository->findByIdentifier($userIdentifier);
        if ($user === null) {
            throw new RecordInDatabaseNotFoundException(
                'fe_user with uid ' . $userIdentifier . ' not found',
                1585479087
            );
        }
        $newsletter = $this->newsletterRepository->findLatestNewsletter();
        if ($newsletter === null) {
            throw new RecordInDatabaseNotFoundException('No newsletter found', 1585479408);
        }
        $this->addUserToQueue($newsletter, $user);
    }

    /**
     * This function is part of the API and can be used from registration extensions to add users to a queue after
     * a registration (e.g.)
     *
     * @param int $userIdentifier fe_users.uid
     * @param int $newsletterIdentifier tx_luxletter_domain_model_newsletter.uid
     * @return void
     * @throws IllegalObjectTypeException
     * @throws RecordInDatabaseNotFoundException
     * @throws ExceptionDbalDriver
     *
     * @api (can be used from third party extensions)
     * @noinspection PhpUnused
     */
    public function addUserWithNewsletterToQueue(int $userIdentifier, int $newsletterIdentifier): void
    {
        /** @var User $user */
        $user = $this->userRepository->findByIdentifier($userIdentifier);
        if ($user === null) {
            throw new RecordInDatabaseNotFoundException(
                'fe_user with uid ' . $userIdentifier . ' not found',
                1585479415
            );
        }
        /** @var Newsletter $newsletter */
        $newsletter = $this->newsletterRepository->findByIdentifier($newsletterIdentifier);
        if ($newsletter === null) {
            throw new RecordInDatabaseNotFoundException(
                Newsletter::TABLE_NAME . ' with uid ' . $newsletterIdentifier . ' not found',
                1585479403
            );
        }
        $this->addUserToQueue($newsletter, $user);
    }

    /**
     * Add a new queue entry with a user and a relation to a newsletter but only if
     *  - user has a valid email
     *  - there is no entry yet
     *
     * @param Newsletter $newsletter
     * @param User $user
     * @return void
     * @throws IllegalObjectTypeException
     * @throws ExceptionDbalDriver
     */
    protected function addUserToQueue(Newsletter $newsletter, User $user): void
    {
        $queueRepository = GeneralUtility::makeInstance(QueueRepository::class);
        if ($user->isValidEmail()
            && $queueRepository->isUserAndNewsletterAlreadyAddedToQueue($user, $newsletter) === false) {
            $queue = GeneralUtility::makeInstance(Queue::class);
            $queue
                ->setEmail($user->getEmail())
                ->setUser($user)
                ->setNewsletter($newsletter)
                ->setDatetime($newsletter->getDatetime());

            /** @var QueueServiceAddUserToQueueEvent $event */
            $event = $this->eventDispatcher->dispatch(GeneralUtility::makeInstance(
                QueueServiceAddUserToQueueEvent::class,
                $queue,
                $user,
                $newsletter
            ));

            $queueRepository->add($event->getQueue());
            $queueRepository->persistAll();
        }
    }
}

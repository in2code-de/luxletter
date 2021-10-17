<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Domain\Repository\QueueRepository;
use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Exception\RecordInDatabaseNotFoundException;
use In2code\Luxletter\Signal\SignalTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
     * @var UserRepository
     */
    protected $userRepository = null;

    /**
     * @var NewsletterRepository
     */
    protected $newsletterRepository = null;

    /**
     * Constructor
     *
     * @param UserRepository $userRepository
     * @param NewsletterRepository $newsletterRepository
     */
    public function __construct(UserRepository $userRepository, NewsletterRepository $newsletterRepository)
    {
        $this->userRepository = $userRepository;
        $this->newsletterRepository = $newsletterRepository;
    }

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
        $users = $this->userRepository->getUsersFromGroup($newsletter->getReceiver()->getUid());
        $this->signalDispatch(__CLASS__, __FUNCTION__, [$users, $newsletter]);
        /** @var User $user */
        foreach ($users as $user) {
            $this->addUserToQueue($newsletter, $user);
        }
    }

    /**
     * This function is part of the API and can be used from registration extensions to add users to a queue after
     * a registration (e.g.)
     *
     * @param int $userIdentifier fe_users.uid
     * @return void
     * @throws RecordInDatabaseNotFoundException
     * @throws Exception
     * @throws IllegalObjectTypeException
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
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws RecordInDatabaseNotFoundException
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
     * @throws Exception
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws IllegalObjectTypeException
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
            $this->signalDispatch(__CLASS__, __FUNCTION__, [$queue, $user, $newsletter]);
            $queueRepository->add($queue);
        }
    }
}

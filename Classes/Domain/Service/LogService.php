<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Model\Log;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\LogRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * Class LogService
 */
class LogService
{
    /**
     * @param Newsletter $newsletter
     * @param User $user
     * @return void
     * @throws IllegalObjectTypeException
     */
    public function logNewsletterDispatch(Newsletter $newsletter, User $user): void
    {
        $this->log($newsletter, $user, Log::STATUS_DISPATCH);
    }

    /**
     * @param Newsletter $newsletter
     * @param User $user
     * @return void
     * @throws IllegalObjectTypeException
     */
    public function logNewsletterDispatchFailure(Newsletter $newsletter, User $user, string $message): void
    {
        $this->log($newsletter, $user, Log::STATUS_DISPATCH_FAILURE, ['exception' => $message]);
    }

    /**
     * Log the opening of a newsletter (via tracking pixel or when clicking a link) only once per newsletter and user
     *
     * @param Newsletter $newsletter
     * @param User $user
     * @return void
     * @throws IllegalObjectTypeException
     * @throws ExceptionDbalDriver
     * @throws DBALException
     */
    public function logNewsletterOpening(Newsletter $newsletter, User $user): void
    {
        $logRepository = GeneralUtility::makeInstance(LogRepository::class);
        if ($logRepository->isLogRecordExisting($newsletter, $user, Log::STATUS_NEWSLETTEROPENING) === false) {
            $this->log($newsletter, $user, Log::STATUS_NEWSLETTEROPENING);
        }
    }

    /**
     * @param Link $link
     * @return void
     * @throws ExceptionDbalDriver
     * @throws IllegalObjectTypeException
     * @throws DBALException
     */
    public function logLinkOpening(Link $link): void
    {
        if ($link->getUser() !== null) {
            $this->logNewsletterOpening($link->getNewsletter(), $link->getUser());
            $this->log($link->getNewsletter(), $link->getUser(), Log::STATUS_LINKOPENING, ['target' => $link->getTarget()]);
        }
    }

    /**
     * @param Newsletter $newsletter
     * @param User $user
     * @return void
     * @throws IllegalObjectTypeException
     */
    public function logUnsubscribe(Newsletter $newsletter, User $user): void
    {
        $this->log($newsletter, $user, Log::STATUS_UNSUBSCRIBE);
    }

    /**
     * @param Newsletter $newsletter
     * @param User $user
     * @param int $status
     * @param array $properties
     * @return void
     * @throws IllegalObjectTypeException
     */
    protected function log(Newsletter $newsletter, User $user, int $status, array $properties = []): void
    {
        $logRepository = GeneralUtility::makeInstance(LogRepository::class);
        $log = GeneralUtility::makeInstance(Log::class)
            ->setStatus($status)
            ->setProperties($properties)
            ->setNewsletter($newsletter)
            ->setUser($user);
        $logRepository->add($log);
        $logRepository->persistAll();
    }
}

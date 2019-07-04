<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Model\Log;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\LogRepository;
use In2code\Luxletter\Utility\ObjectUtility;
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
     * Log the opening of a newsletter (via tracking pixel) only once per newsletter and user
     *
     * @param Newsletter $newsletter
     * @param User $user
     * @return void
     * @throws IllegalObjectTypeException
     */
    public function logNewsletterOpening(Newsletter $newsletter, User $user): void
    {
        $logRepository = ObjectUtility::getObjectManager()->get(LogRepository::class);
        if ($logRepository->isLogRecordExisting($newsletter, $user, Log::STATUS_NEWSLETTEROPENING) === false) {
            $this->log($newsletter, $user, Log::STATUS_NEWSLETTEROPENING);
        }
    }

    /**
     * @param Link $link
     * @return void
     * @throws IllegalObjectTypeException
     */
    public function logLinkOpening(Link $link): void
    {
        $this->log($link->getNewsletter(), $link->getUser(), Log::STATUS_LINKOPENING, ['target' => $link->getTarget()]);
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
        $logRepository = ObjectUtility::getObjectManager()->get(LogRepository::class);
        $log = ObjectUtility::getObjectManager()->get(Log::class)
            ->setStatus($status)
            ->setProperties($properties)
            ->setNewsletter($newsletter)
            ->setUser($user);
        $logRepository->add($log);
        $logRepository->persistAll();
    }
}

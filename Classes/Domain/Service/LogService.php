<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Luxletter\Domain\Model\Log;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\LogRepository;
use In2code\Luxletter\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LogService
{
    public function logNewsletterDispatch(Newsletter $newsletter, User $user): void
    {
        $this->log($newsletter->getUid(), $user->getUid(), Log::STATUS_DISPATCH);
    }

    public function logNewsletterDispatchFailure(Newsletter $newsletter, User $user, string $message): void
    {
        $this->log($newsletter->getUid(), $user->getUid(), Log::STATUS_DISPATCH_FAILURE, ['exception' => $message]);
    }

    /**
     * Log the opening of a newsletter (via tracking pixel or when clicking a link) only once per newsletter and user
     *
     * @param int $newsletterIdentifier
     * @param int $userIdentifier
     * @return void
     * @throws ExceptionDbal
     */
    public function logNewsletterOpening(int $newsletterIdentifier, int $userIdentifier): void
    {
        $logRepository = GeneralUtility::makeInstance(LogRepository::class);
        if ($logRepository->isLogRecordExisting($newsletterIdentifier, $userIdentifier, Log::STATUS_NEWSLETTEROPENING) === false) {
            $this->log($newsletterIdentifier, $userIdentifier, Log::STATUS_NEWSLETTEROPENING);
        }
    }

    /**
     * @param array $link
     * @return void
     * @throws ExceptionDbal
     */
    public function logLinkOpening(array $link): void
    {
        if (($link['user'] ?? 0) > 0 && ($link['newsletter'] ?? 0) > 0) {
            $this->logNewsletterOpening($link['newsletter'], $link['user']);
            $this->log($link['newsletter'], $link['user'], Log::STATUS_LINKOPENING, ['target' => $link['target']]);
        }
    }

    public function logUnsubscribe(Newsletter $newsletter, User $user): void
    {
        $this->log($newsletter->getUid(), $user->getUid(), Log::STATUS_UNSUBSCRIBE);
    }

    protected function log(int $newsletterIdentifier, int $userIdentifier, int $status, array $properties = []): void
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Log::TABLE_NAME);
        $queryBuilder
            ->insert(Log::TABLE_NAME)
            ->values([
                'status' => $status,
                'properties' => json_encode($properties),
                'user' => $userIdentifier,
                'newsletter' => $newsletterIdentifier,
            ])
            ->executeStatement();
    }
}

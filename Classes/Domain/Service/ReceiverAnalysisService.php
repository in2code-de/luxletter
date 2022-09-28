<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\LogRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class ReceiverAnalysisService
 */
class ReceiverAnalysisService
{
    /**
     * Returns activities per user like:
     *  [
     *      2 => [
     *          'newlettersdispatched' => 8,
     *          'activities' => 9
     *      ],
     *      12 => [
     *          'newlettersdispatched' => 123,
     *          'activities' => 125
     *      ]
     *  ]
     * @param QueryResultInterface $users
     * @return array
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function getActivitiesStatistic(QueryResultInterface $users): array
    {
        $logRepository = GeneralUtility::makeInstance(LogRepository::class);
        $activities = [];
        /** @var User $user */
        foreach ($users as $user) {
            $activities[$user->getUid()] = [
                'newlettersdispatched' => $logRepository->findRawByUser($user, [100]),
                'activities' => $logRepository->findRawByUser($user, [], [100]),
            ];
        }
        return $activities;
    }
}

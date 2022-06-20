<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Widget\DataProvider;

use Doctrine\DBAL\DBALException;
use In2code\Luxletter\Domain\Repository\LogRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;

/**
 * Class ReceiverDataProvider
 * @noinspection PhpUnused
 */
class ReceiverDataProvider implements NumberWithIconDataProviderInterface
{
    /**
     * @return int
     * @throws DBALException
     */
    public function getNumber(): int
    {
        $logRepository = GeneralUtility::makeInstance(LogRepository::class);
        return $logRepository->getNumberOfReceivers();
    }
}

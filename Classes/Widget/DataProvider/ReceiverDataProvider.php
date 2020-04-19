<?php
declare(strict_types=1);
namespace In2code\Luxletter\Widget\DataProvider;

use Doctrine\DBAL\DBALException;
use In2code\Luxletter\Domain\Repository\LogRepository;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class ReceiverDataProvider
 * @noinspection PhpUnused
 */
class ReceiverDataProvider implements NumberWithIconDataProviderInterface
{
    /**
     * @return int
     * @throws DBALException
     * @throws Exception
     */
    public function getNumber(): int
    {
        $logRepository = ObjectUtility::getObjectManager()->get(LogRepository::class);
        return $logRepository->getNumberOfReceivers();
    }
}

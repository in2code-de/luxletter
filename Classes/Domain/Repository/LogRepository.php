<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Luxletter\Domain\Model\Log;
use In2code\Luxletter\Utility\DatabaseUtility;

/**
 * Class LogRepository
 */
class LogRepository extends AbstractRepository
{

    /**
     * @return int
     * @throws DBALException
     */
    public function getNumberOfReceivers(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        return (int)$connection->executeQuery(
            'select count(DISTINCT user) from ' . Log::TABLE_NAME . ' where deleted=0;'
        )->fetchColumn(0);
    }
}

<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Luxletter\Domain\Model\Log;
use In2code\Luxletter\Utility\DatabaseUtility;
use In2code\Luxletter\Utility\ObjectUtility;

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
            'select count(DISTINCT user) from ' . Log::TABLE_NAME .
            ' where deleted=0 and status=' . Log::STATUS_DISPATCH . ';'
        )->fetchColumn(0);
    }

    /**
     * @param int $limit
     * @return array
     * @throws DBALException
     */
    public function getGroupedLinksByHref(int $limit = 8): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        $results = (array)$connection->executeQuery(
            'select count(*) as count, properties, newsletter from ' . Log::TABLE_NAME .
            ' where deleted=0 and status=' . Log::STATUS_LINKOPENING .
            ' group by properties order by count desc limit ' . $limit
        )->fetchAll();
        $nlRepository = ObjectUtility::getObjectManager()->get(NewsletterRepository::class);
        foreach ($results as &$result) {
            $result['target'] = json_decode($result['properties'], true)['target'];
            $result['newsletter'] = $nlRepository->findByUid($result['newsletter']);
        }
        return $results;
    }
}

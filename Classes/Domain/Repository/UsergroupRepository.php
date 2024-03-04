<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Luxletter\Domain\Model\Usergroup;
use In2code\Luxletter\Domain\Service\PermissionTrait;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\ArrayUtility;
use In2code\Luxletter\Utility\DatabaseUtility;

class UsergroupRepository extends AbstractRepository
{
    use PermissionTrait;

    public function findByIdentifiersAndKeepOrderings(array $usergroupIdentifiers): array
    {
        $result = [];
        if ($usergroupIdentifiers !== []) {
            $connection = DatabaseUtility::getConnectionForTable(Usergroup::TABLE_NAME);
            $sql = 'select * from ' . Usergroup::TABLE_NAME
                . ' where uid in (' . ArrayUtility::convertArrayToIntegerList($usergroupIdentifiers) . ')'
                . ' order by FIELD(uid, ' . ArrayUtility::convertArrayToIntegerList($usergroupIdentifiers) . ')';
            $records = $connection->executeQuery($sql)->fetchAllAssociative();
            foreach ($records as $record) {
                $user = $this->findByUid($record['uid']);
                if ($user !== null) {
                    $result[] = $user;
                }
            }
        }
        return $result;
    }

    /**
     * Example return values like
     *  [
     *      123 => 'Usergroup title A',
     *      234 => 'Usergroup title B',
     *  ]
     *
     * @return array
     * @throws ExceptionDbal
     * @throws MisconfigurationException
     */
    public function getReceiverGroups(): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Usergroup::TABLE_NAME);
        $groups = $queryBuilder
            ->select('uid', 'title')
            ->from(Usergroup::TABLE_NAME)
            ->where('luxletter_receiver=1')
            ->orderBy('title', 'ASC')
            ->executeQuery()
            ->fetchAllKeyValue();
        return $this->filterRecords($groups, Usergroup::TABLE_NAME);
    }
}

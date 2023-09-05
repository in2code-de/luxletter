<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Luxletter\Domain\Model\Usergroup;
use In2code\Luxletter\Utility\ArrayUtility;
use In2code\Luxletter\Utility\DatabaseUtility;

class UsergroupRepository extends AbstractRepository
{
    public function findByIdentifiersAndKeepOrderings(array $usergroupIdentifiers): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Usergroup::TABLE_NAME);
        $sql = 'select * from ' . Usergroup::TABLE_NAME
            . ' where uid in (' . ArrayUtility::convertArrayToIntegerList($usergroupIdentifiers) . ')'
            . ' order by FIELD(uid, ' . ArrayUtility::convertArrayToIntegerList($usergroupIdentifiers) . ')';
        $records = $connection->executeQuery($sql)->fetchAllAssociative();
        $result = [];
        foreach ($records as $record) {
            $user = $this->findByUid($record['uid']);
            if ($user !== null) {
                $result[] = $user;
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
     * @throws DBALException
     */
    public function getReceiverGroups(): array
    {
        $groups = [];
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Usergroup::TABLE_NAME);
        $statement = $queryBuilder
            ->select('uid', 'title')
            ->from(Usergroup::TABLE_NAME)
            ->where('luxletter_receiver=1')
            ->orderBy('title', 'ASC')
            ->executeQuery();
        while ($row = $statement->fetch()) {
            $groups[$row['uid']] = $row['title'];
        }
        return $groups;
    }
}

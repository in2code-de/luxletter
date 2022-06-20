<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Repository;

use In2code\Luxletter\Domain\Model\Usergroup;
use In2code\Luxletter\Utility\DatabaseUtility;

/**
 * Class UsergroupRepository
 */
class UsergroupRepository extends AbstractRepository
{
    /**
     * @return array
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
            ->execute();
        while ($row = $statement->fetch()) {
            $groups[$row['uid']] = $row['title'];
        }
        return $groups;
    }
}

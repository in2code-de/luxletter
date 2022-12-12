<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Luxletter\Domain\Model\Usergroup;
use In2code\Luxletter\Utility\DatabaseUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class UsergroupRepository
 */
class UsergroupRepository extends AbstractRepository
{
    /**
     * @param array $usergroupIdentifiers
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function findByIdentifiers(array $usergroupIdentifiers): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->in('uid', $usergroupIdentifiers));
        return $query->execute();
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

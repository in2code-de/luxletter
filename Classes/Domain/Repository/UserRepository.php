<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use In2code\Luxletter\Domain\Model\Dto\Filter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Utility\DatabaseUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class UserRepository
 */
class UserRepository extends AbstractRepository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'lastName' => QueryInterface::ORDER_ASCENDING,
    ];

    /**
     * @param int $groupIdentifier
     * @param int $language -1 = all, otherwise only the users with the specific language are selected
     * @param int $limit
     * @return QueryResultInterface
     */
    public function getUsersFromGroup(int $groupIdentifier, int $language, int $limit = 0): QueryResultInterface
    {
        $query = $this->createQuery();
        $and = [$query->equals('usergroup.uid', $groupIdentifier)];
        if ($language !== -1) {
            $and[] = $query->equals('luxletterLanguage', $language);
        }
        $query->matching($query->logicalAnd($and));
        if ($limit > 0) {
            $query->setLimit($limit);
        }
        return $query->execute();
    }

    /**
     * @param int $groupIdentifier
     * @return int
     * @throws DBALException
     * @throws Exception
     */
    public function getUserAmountFromGroup(int $groupIdentifier): int
    {
        $connection = DatabaseUtility::getConnectionForTable(User::TABLE_NAME);
        /** @noinspection SqlDialectInspection */
        $query = 'select count(uid) from ' . User::TABLE_NAME . ' ';
        $query .= 'where find_in_set(' . $groupIdentifier . ',usergroup) and deleted=0 and disable=0';
        return (int)$connection->executeQuery($query)->fetchOne();
    }

    /**
     * Get all luxletter receiver users
     *
     * @param Filter $filter
     * @return QueryResultInterface
     * @throws InvalidQueryException
     */
    public function getUsersByFilter(Filter $filter): QueryResultInterface
    {
        $query = $this->createQuery();
        $this->buildQueryForFilter($filter, $query);
        return $query->execute();
    }

    /**
     * @param Filter $filter
     * @param QueryInterface $query
     * @return void
     * @throws InvalidQueryException
     */
    protected function buildQueryForFilter(Filter $filter, QueryInterface $query): void
    {
        $and = [
            $query->equals('usergroup.luxletterReceiver', true),
        ];
        if ($filter->getSearchterms() !== []) {
            foreach ($filter->getSearchterms() as $searchterm) {
                $or = [
                    $query->like('username', '%' . $searchterm . '%'),
                    $query->like('email', '%' . $searchterm . '%'),
                    $query->like('name', '%' . $searchterm . '%'),
                    $query->like('firstName', '%' . $searchterm . '%'),
                    $query->like('middleName', '%' . $searchterm . '%'),
                    $query->like('lastName', '%' . $searchterm . '%'),
                    $query->like('address', '%' . $searchterm . '%'),
                    $query->like('title', '%' . $searchterm . '%'),
                    $query->like('company', '%' . $searchterm . '%'),
                ];
                $and[] = $query->logicalOr($or);
            }
        }
        if ($filter->getUsergroup() !== null) {
            $and[] = $query->contains('usergroup', $filter->getUsergroup());
        }
        $constraint = $query->logicalAnd($and);
        $query->matching($constraint);

        $query->setLimit(1000);
    }
}

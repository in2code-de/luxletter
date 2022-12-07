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
     * Get users grouped by email from groupIdentifiers
     * We don't use `group by` any more because of the problems that came with "sql_mode=only_full_group_by"
     *
     * @param int[] $groupIdentifiers
     * @param int $language -1 = all, otherwise only the users with the specific language are selected
     * @param int $limit
     * @return array
     */
    public function getUsersFromGroups(array $groupIdentifiers, int $language, int $limit = 0): array
    {
        if ($groupIdentifiers === []) {
            return [];
        }

        $query = $this->createQuery();
        $constraints = [
            $query->like('email', '%@%'),
        ];
        if ($language !== -1) {
            $constraints[] = $query->in('luxletter_language', [-1, $language]);
        }

        $subConstraints = [];
        foreach ($groupIdentifiers as $identifier) {
            $subConstraints[] = $query->contains('usergroup', $identifier);
        }
        $constraints[] = $query->logicalOr($subConstraints);

        if ($limit > 0) {
            $query->setLimit($limit * 10);
        }
        $query->matching($query->logicalAnd($constraints));
        $query->setOrderings(['email' => QueryInterface::ORDER_ASCENDING]);
        $users = $query->execute()->toArray();

        return $this->groupResultByEmail($users, $limit);
    }

    /**
     * @param array $users
     * @param int $limit
     * @return array
     */
    protected function groupResultByEmail(array $users, int $limit): array
    {
        $result = [];
        foreach ($users as $user) {
            if (array_key_exists($user->getEmail(), $result) === false) {
                $result[$user->getEmail()] = $user;
            }
            if ($limit > 0 && count($result) >= $limit) {
                break;
            }
        }
        return $result;
    }

    /**
     * @param int[] $groupIdentifiers
     * @return int
     * @throws DBALException
     * @throws Exception
     */
    public function getUserAmountFromGroups(array $groupIdentifiers): int
    {
        if ($groupIdentifiers !== []) {
            $connection = DatabaseUtility::getConnectionForTable(User::TABLE_NAME);
            /** @noinspection SqlDialectInspection */
            $sql = 'select count(distinct email) from ' . User::TABLE_NAME;
            $sub = '';
            foreach ($groupIdentifiers as $identifier) {
                if ($sub !== '') {
                    $sub .= ' or ';
                }
                $sub .= 'find_in_set(' . (int)$identifier . ',usergroup)';
            }
            $sql .= ' where deleted=0 and disable=0 and email like "%@%" and (' . $sub . ')';
            return (int)$connection->executeQuery($sql)->fetchOne();
        }
        return 0;
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

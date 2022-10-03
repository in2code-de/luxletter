<?php

declare(strict_types=1);
namespace In2code\Luxletter\Utility;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class DatabaseUtility
 */
class DatabaseUtility
{
    /**
     * @param string $tableName
     * @param bool $removeRestrictions
     * @return QueryBuilder
     */
    public static function getQueryBuilderForTable(string $tableName, bool $removeRestrictions = false): QueryBuilder
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
        if ($removeRestrictions === true) {
            $queryBuilder->getRestrictions()->removeAll();
        }
        return $queryBuilder;
    }

    /**
     * @param string $tableName
     * @return Connection
     */
    public static function getConnectionForTable(string $tableName): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($tableName);
    }

    /**
     * @param string $fieldName
     * @param string $tableName
     * @return bool
     * @throws ExceptionDbalDriver
     * @throws ExceptionDbal
     */
    public static function isFieldExistingInTable(string $fieldName, string $tableName): bool
    {
        $found = false;
        $connection = self::getConnectionForTable($tableName);
        $queryResult = $connection->executeQuery('describe ' . $tableName . ';')->fetchAllAssociative();
        foreach ($queryResult as $fieldProperties) {
            if ($fieldProperties['Field'] === $fieldName) {
                $found = true;
                break;
            }
        }
        return $found;
    }

    /**
     * @param string $fieldName
     * @param string $tableName
     * @return bool
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public static function isFieldFilled(string $fieldName, string $tableName): bool
    {
        if (self::isFieldExistingInTable($fieldName, $tableName)) {
            $queryBuilder = self::getQueryBuilderForTable($tableName, true);
            return (int)$queryBuilder
                ->count($fieldName)
                ->from($tableName)
                ->where($fieldName . ' != "" and ' . $fieldName . ' != 0')
                ->execute()
                ->fetchOne() > 0;
        }
        return false;
    }
}

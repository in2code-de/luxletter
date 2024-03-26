<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\BackendUserUtility;
use In2code\Luxletter\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

trait PermissionTrait
{
    /**
     * Remove unauthorized records from array
     *
     * @param array $rows
     * @param string $table
     * @return array
     * @throws ExceptionDbal
     * @throws MisconfigurationException
     */
    private function filterRecords(array $rows, string $table): array
    {
        if (BackendUserUtility::isAdministrator()) {
            return $rows;
        }

        foreach ($rows as $key => $row) {
            $identifier = $this->getIdentifierFromArrayOrObject($row, $key);
            if ($this->isAuthenticatedForRecord($identifier, $table) === false) {
                unset($rows[$key]);
            }
        }
        return $rows;
    }

    /**
     * @param $object
     * @param $key
     * @return int
     * @throws MisconfigurationException
     */
    protected function getIdentifierFromArrayOrObject($object, $key): int
    {
        if (is_array($object)) { // AllAssociative
            if (array_key_exists('uid', $object)) {
                return $object['uid'];
            }
        } elseif (is_string($object) || is_int($object)) { // KeyValue
            return (int)$key;
        } elseif (is_a($object, AbstractEntity::class)) { // DomainObject
            return $object->getUid();
        }
        throw new MisconfigurationException('Object not supported in ' . __CLASS__, 1709566644);
    }

    /**
     * @param int $identifier
     * @param string $table
     * @return bool
     * @throws ExceptionDbal
     */
    private function isAuthenticatedForRecord(int $identifier, string $table): bool
    {
        if (BackendUserUtility::isAdministrator()) {
            return true;
        }

        $pageIdentifier = $this->getPageIdentifierFromRecord($identifier, $table);
        if ($pageIdentifier > 0) {
            return $this->isAuthenticatedForPageRow($this->getPageRowFromPageIdentifier($pageIdentifier));
        }
        return false;
    }

    private function isAuthenticatedForPageRow(array $pageRecord): bool
    {
        if (BackendUserUtility::isAdministrator()) {
            return true;
        }

        $beuserAuthentication = BackendUserUtility::getBackendUserAuthentication();
        return $beuserAuthentication !== null &&
            $beuserAuthentication->doesUserHaveAccess($pageRecord, Permission::PAGE_SHOW);
    }

    /**
     * @param int $identifier
     * @param string $table
     * @return int
     * @throws ExceptionDbal
     */
    protected function getPageIdentifierFromRecord(int $identifier, string $table): int
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable($table);
        return (int)$queryBuilder
            ->select('pid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($identifier, Connection::PARAM_INT))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();
    }

    /**
     * @param int $identifier
     * @return array|int
     * @throws ExceptionDbal
     */
    protected function getPageRowFromPageIdentifier(int $identifier): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable('pages');
        return (array)$queryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($identifier, Connection::PARAM_INT))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();
    }
}

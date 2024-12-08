<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Exception\ArgumentMissingException;
use In2code\Luxletter\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

class LinkRepository extends AbstractRepository
{
    public function findOneByHashRaw(string $hash): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Link::TABLE_NAME);
        $row = $queryBuilder
            ->select('*')
            ->from(Link::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('hash', $queryBuilder->createNamedParameter($hash, Connection::PARAM_STR))
            )
            ->orderBy('uid', 'desc')
            ->executeQuery()
            ->fetchAssociative();
        if ($row === false) {
            $row = [];
        }
        return $row;
    }
    /**
     * @param string $hash
     * @return bool
     */
    public function isHashAlreadyExisting(string $hash): bool
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $link = $this->findOneByHash($hash);
        return $link !== null;
    }

    /**
     * Add link only if it is not yet existing (check by given hash)
     *
     * @param object $object
     * @return void
     * @throws IllegalObjectTypeException
     * @throws ArgumentMissingException
     * @throws Exception
     */
    public function add($object)
    {
        if ($object->getHash() === '') {
            throw new ArgumentMissingException('Cannot persist a non hashed link object', 1561838265);
        }
        if ($this->isHashAlreadyExisting($object->getHash()) === false) {
            parent::add($object);
            $this->persistAll();
        }
    }
}

<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * Class LinkRepository
 */
class LinkRepository extends AbstractRepository
{

    /**
     * @param string $hash
     * @return bool
     */
    public function isHashAlreadyExisting(string $hash): bool
    {
        /** @noinspection PhpMethodParametersCountMismatchInspection PhpParamsInspection */
        $link = $this->findOneByHash($hash);
        return $link !== null;
    }

    /**
     * Add link only if it is not yet existing (check by given hash)
     *
     * @param object $object
     * @return void
     * @throws IllegalObjectTypeException
     */
    public function add($object)
    {
        if ($object->getHash() === '') {
            throw new \LogicException('Cannot persist a non hashed link object', 1561838265);
        }
        if ($this->isHashAlreadyExisting($object->getHash()) === false) {
            parent::add($object);
            $this->persistAll();
        }
    }
}

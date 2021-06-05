<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use In2code\Luxletter\Exception\ArgumentMissingException;
use TYPO3\CMS\Extbase\Object\Exception;
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

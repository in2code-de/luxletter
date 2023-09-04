<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Factory;

use In2code\Luxletter\Domain\Repository\UsergroupRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class UsergroupFactory
{
    protected UsergroupRepository $usergroupRepository;

    public function __construct(UsergroupRepository $usergroupRepository)
    {
        $this->usergroupRepository = $usergroupRepository;
    }

    public function convertUsergroupIdentifiersToObjectStorage(array $usergroupIdentifiers)
    {
        $queryResult = $this->usergroupRepository->findByIdentifiers($usergroupIdentifiers);
        $objectStorage = GeneralUtility::makeInstance(ObjectStorage::class);
        if ($queryResult !== null) {
            foreach ($queryResult as $object) {
                $objectStorage->attach($object);
            }
        }
        return $objectStorage;
    }
}

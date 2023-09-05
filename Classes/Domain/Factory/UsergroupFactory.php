<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Factory;

use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Model\Usergroup;
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

    public function updateUsergroupsInUser(User $user, array $usergroupIdentifiers, array $allowedUsergroupIdentifiers)
    {
        $thirdGroups = $this->getThirdUsergroupsFromUser($user, $allowedUsergroupIdentifiers);
        $firstGroups = $usergroupIdentifiers;
        $newGroups = $this->convertUsergroupIdentifiersToObjectStorage(array_merge($firstGroups, $thirdGroups));
        $user->setUsergroup($newGroups);
    }

    /**
     * Get all usergroups that are not configured but should be kept (e.g. third usergroups that are not relevant for
     * LUXletter but should be kept)
     *
     * @param User $user
     * @param array $allowedUsergroupIdentifiers
     * @return array
     */
    protected function getThirdUsergroupsFromUser(User $user, array $allowedUsergroupIdentifiers): array
    {
        $groups = [];
        /** @var Usergroup $group */
        foreach ($user->getUsergroup() as $usergroup) {
            if (in_array($usergroup->getUid(), $allowedUsergroupIdentifiers) === false) {
                $groups[] = $usergroup->getUid();
            }
        }
        return $groups;
    }

    public function convertUsergroupIdentifiersToObjectStorage(array $usergroupIdentifiers): ObjectStorage
    {
        $queryResult = $this->usergroupRepository->findByIdentifiersAndKeepOrderings($usergroupIdentifiers);
        $objectStorage = GeneralUtility::makeInstance(ObjectStorage::class);
        foreach ($queryResult as $object) {
            $objectStorage->attach($object);
        }
        return $objectStorage;
    }
}

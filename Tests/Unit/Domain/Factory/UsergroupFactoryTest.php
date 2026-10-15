<?php

namespace In2code\Luxletter\Tests\Unit\Domain\Factory;

use In2code\Luxletter\Domain\Factory\UsergroupFactory;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\UsergroupRepository;
use In2code\Luxletter\Tests\Unit\Fixtures\DomainObjectTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(UsergroupFactory::class)]
class UsergroupFactoryTest extends UnitTestCase
{
    use DomainObjectTrait;

    protected UsergroupRepository&MockObject $usergroupRepository;
    protected UsergroupFactory $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->usergroupRepository = $this->createMock(UsergroupRepository::class);
        $this->subject = new UsergroupFactory($this->usergroupRepository);
    }

    /**
     * Usergroups that are not configured in the unsubscribe plugin (e.g. a customer group) must survive an update
     *
     * @return void
     */
    public function testUpdateUsergroupsInUserReplacesAllowedGroupsAndKeepsThirdPartyGroups(): void
    {
        $user = new User();
        $user->addUsergroup($this->getUsergroup(1));
        $user->addUsergroup($this->getUsergroup(7));
        $newGroups = [$this->getUsergroup(2), $this->getUsergroup(7)];
        $this->usergroupRepository
            ->expects(self::once())
            ->method('findByIdentifiersAndKeepOrderings')
            ->with([2, 7])
            ->willReturn($newGroups);

        $this->subject->updateUsergroupsInUser($user, [2], [1, 2, 3]);

        self::assertSame($newGroups, $user->getUsergroup()->toArray());
    }

    /**
     * @return void
     */
    public function testUpdateUsergroupsInUserRemovesAllAllowedGroupsIfNothingIsSelected(): void
    {
        $user = new User();
        $user->addUsergroup($this->getUsergroup(1));
        $user->addUsergroup($this->getUsergroup(2));
        $user->addUsergroup($this->getUsergroup(7));
        $this->usergroupRepository
            ->expects(self::once())
            ->method('findByIdentifiersAndKeepOrderings')
            ->with([7])
            ->willReturn([$this->getUsergroup(7)]);

        $this->subject->updateUsergroupsInUser($user, [], [1, 2, 3]);

        self::assertCount(1, $user->getUsergroup());
    }

    /**
     * @return void
     */
    public function testConvertUsergroupIdentifiersToObjectStorageKeepsOrderingOfRepository(): void
    {
        $usergroups = [$this->getUsergroup(3), $this->getUsergroup(1)];
        $this->usergroupRepository->method('findByIdentifiersAndKeepOrderings')->willReturn($usergroups);

        $objectStorage = $this->subject->convertUsergroupIdentifiersToObjectStorage([3, 1]);

        self::assertSame($usergroups, $objectStorage->toArray());
    }
}

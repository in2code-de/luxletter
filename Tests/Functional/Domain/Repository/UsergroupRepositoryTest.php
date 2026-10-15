<?php

namespace In2code\Luxletter\Tests\Functional\Domain\Repository;

use In2code\Luxletter\Domain\Model\Usergroup;
use In2code\Luxletter\Domain\Repository\UsergroupRepository;
use In2code\Luxletter\Tests\Functional\AbstractFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UsergroupRepository::class)]
class UsergroupRepositoryTest extends AbstractFunctionalTestCase
{
    protected UsergroupRepository $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/Receivers.csv');
        $this->subject = $this->get(UsergroupRepository::class);
    }

    /**
     * @return void
     */
    public function testFindByIdentifiersAndKeepOrderingsKeepsGivenOrderAndSkipsUnknownGroups(): void
    {
        $usergroups = $this->subject->findByIdentifiersAndKeepOrderings([3, 99, 1]);

        self::assertSame([3, 1], array_map(static fn (Usergroup $usergroup): int => $usergroup->getUid(), $usergroups));
    }

    /**
     * @return void
     */
    public function testFindByIdentifiersAndKeepOrderingsReturnsEmptyArrayWithoutIdentifiers(): void
    {
        self::assertSame([], $this->subject->findByIdentifiersAndKeepOrderings([]));
    }

    /**
     * @return void
     */
    public function testGetReceiverGroupsReturnsOnlyGroupsWithReceiverFlagOrderedByTitle(): void
    {
        self::assertSame([1 => 'Newsletter A', 3 => 'Newsletter B'], $this->subject->getReceiverGroups());
    }
}

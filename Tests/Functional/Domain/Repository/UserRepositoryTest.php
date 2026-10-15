<?php

namespace In2code\Luxletter\Tests\Functional\Domain\Repository;

use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Tests\Functional\AbstractFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(UserRepository::class)]
class UserRepositoryTest extends AbstractFunctionalTestCase
{
    protected UserRepository $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/Receivers.csv');
        $this->subject = $this->get(UserRepository::class);
    }

    public static function getUsersFromGroupsDataProvider(): array
    {
        return [
            // Users without valid email, disabled and deleted users are skipped, duplicate emails are merged
            'all languages' => [
                [1, 3],
                -1,
                ['alice@example.org', 'bob@example.org', 'dave@example.org'],
            ],
            // Users with language -1 get newsletters in every language
            'default language' => [
                [1, 3],
                0,
                ['alice@example.org', 'dave@example.org'],
            ],
            'other language' => [
                [1, 3],
                1,
                ['bob@example.org', 'dave@example.org'],
            ],
            'group without receiver flag' => [
                [2],
                -1,
                ['bob@example.org', 'carol@example.org'],
            ],
            'no groups' => [
                [],
                -1,
                [],
            ],
        ];
    }

    /**
     * @param array $groups
     * @param int $language
     * @param array $expectedEmails
     * @return void
     */
    #[DataProvider('getUsersFromGroupsDataProvider')]
    public function testGetUsersFromGroups(array $groups, int $language, array $expectedEmails): void
    {
        self::assertSame($expectedEmails, array_keys($this->subject->getUsersFromGroups($groups, $language)));
    }

    /**
     * @return void
     */
    public function testGetUsersFromGroupsRespectsLimit(): void
    {
        self::assertSame(
            ['alice@example.org', 'bob@example.org'],
            array_keys($this->subject->getUsersFromGroups([1, 3], -1, 2))
        );
    }

    public static function getUserAmountFromGroupsDataProvider(): array
    {
        return [
            'receiver groups' => [[1, 3], 3],
            'group without receiver flag' => [[2], 2],
            'no groups' => [[], 0],
        ];
    }

    /**
     * @param array $groups
     * @param int $expectedAmount
     * @return void
     */
    #[DataProvider('getUserAmountFromGroupsDataProvider')]
    public function testGetUserAmountFromGroupsCountsDistinctValidEmails(array $groups, int $expectedAmount): void
    {
        self::assertSame($expectedAmount, $this->subject->getUserAmountFromGroups($groups));
    }
}

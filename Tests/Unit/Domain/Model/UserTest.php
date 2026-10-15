<?php

namespace In2code\Luxletter\Tests\Unit\Domain\Model;

use DateTime;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Exception\UserValuesAreMissingException;
use In2code\Luxletter\Tests\Unit\Fixtures\DomainObjectTrait;
use In2code\Luxletter\Tests\Unit\Fixtures\EncryptionKeyTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(User::class)]
class UserTest extends UnitTestCase
{
    use DomainObjectTrait;
    use EncryptionKeyTrait;

    /**
     * @return void
     */
    public function testGetUnsubscribeHashThrowsExceptionWithoutCrdate(): void
    {
        $this->expectException(UserValuesAreMissingException::class);
        $this->expectExceptionCode(1574764265);
        $this->getUser(5)->getUnsubscribeHash();
    }

    /**
     * @return void
     */
    public function testGetUnsubscribeHashIsBuiltFromUidCrdateAndEncryptionKey(): void
    {
        $this->addEncryptionKey('secret');
        $user = $this->getUser(5);
        $user->setCrdate(new DateTime('@1700000000'));

        self::assertSame(hash('sha256', '5/1700000000/secret'), $user->getUnsubscribeHash());
    }
}

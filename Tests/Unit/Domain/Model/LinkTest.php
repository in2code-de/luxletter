<?php

namespace In2code\Luxletter\Tests\Unit\Domain\Model;

use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Tests\Unit\Fixtures\DomainObjectTrait;
use In2code\Luxletter\Tests\Unit\Fixtures\EncryptionKeyTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(Link::class)]
class LinkTest extends UnitTestCase
{
    use DomainObjectTrait;
    use EncryptionKeyTrait;

    /**
     * @return void
     */
    public function testSetTargetWithoutNewsletterAndUserDoesNotSetHash(): void
    {
        $link = new Link();
        $link->setTarget('https://www.in2code.de');

        self::assertSame('https://www.in2code.de', $link->getTarget());
        self::assertSame('', $link->getHash());
    }

    /**
     * @return void
     */
    public function testSetTargetSetsHashFromTargetUserNewsletterAndEncryptionKey(): void
    {
        $this->addEncryptionKey('secret');
        $link = (new Link())->setNewsletter($this->getNewsletter(3))->setUser($this->getUser(5));
        $link->setTarget('https://www.in2code.de');

        self::assertSame(hash('sha256', 'https://www.in2code.de/5/3/secret'), $link->getHash());
    }

    /**
     * @return void
     */
    public function testSetTargetDoesNotOverwriteExistingHash(): void
    {
        $link = (new Link())->setNewsletter($this->getNewsletter(3))->setUser($this->getUser(5));
        $link->setHash('existing');
        $link->setTarget('https://www.in2code.de');

        self::assertSame('existing', $link->getHash());
    }
}

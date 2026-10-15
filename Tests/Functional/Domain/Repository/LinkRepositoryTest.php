<?php

namespace In2code\Luxletter\Tests\Functional\Domain\Repository;

use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Repository\LinkRepository;
use In2code\Luxletter\Exception\ArgumentMissingException;
use In2code\Luxletter\Tests\Functional\AbstractFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LinkRepository::class)]
class LinkRepositoryTest extends AbstractFunctionalTestCase
{
    protected LinkRepository $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Link.csv');
        $this->subject = $this->get(LinkRepository::class);
    }

    /**
     * @return void
     */
    public function testFindOneByHashRawReturnsLinkRecord(): void
    {
        $link = $this->subject->findOneByHashRaw('hash2');

        self::assertSame(2, $link['uid']);
        self::assertSame('https://www.in2code.de/two', $link['target']);
    }

    /**
     * @return void
     */
    public function testFindOneByHashRawReturnsEmptyArrayForUnknownHash(): void
    {
        self::assertSame([], $this->subject->findOneByHashRaw('unknown'));
    }

    /**
     * @return void
     */
    public function testAddPersistsLinkWithNewHash(): void
    {
        $this->subject->add($this->getLink('hash3', 'https://www.in2code.de/three'));

        self::assertSame('https://www.in2code.de/three', $this->subject->findOneByHashRaw('hash3')['target']);
    }

    /**
     * @return void
     */
    public function testAddDoesNotPersistLinkWithExistingHash(): void
    {
        $this->subject->add($this->getLink('hash1', 'https://www.in2code.de/changed'));

        self::assertSame(2, $this->subject->countAll());
        self::assertSame('https://www.in2code.de/one', $this->subject->findOneByHashRaw('hash1')['target']);
    }

    /**
     * @return void
     */
    public function testAddThrowsExceptionForLinkWithoutHash(): void
    {
        $this->expectException(ArgumentMissingException::class);
        $this->expectExceptionCode(1561838265);
        $this->subject->add($this->getLink('', 'https://www.in2code.de'));
    }

    protected function getLink(string $hash, string $target): Link
    {
        return (new Link())->setHash($hash)->setTarget($target);
    }
}

<?php

namespace In2code\Luxletter\Tests\Unit\Domain\Service\BodytextManipulation;

use In2code\Luxletter\Domain\Model\Configuration;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Service\BodytextManipulation\LinkHashing;
use In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\BodytextManipulation\LinkHashingFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(LinkHashing::class)]
class LinkHashingTest extends UnitTestCase
{
    protected AccessibleObjectInterface&MockObject&LinkHashingFixture $subject;

    public function setUp(): void
    {
        parent::setUp();
        $configuration = new Configuration();
        $newsletter = new Newsletter();
        $newsletter->setConfiguration($configuration);
        $this->subject = $this->getAccessibleMock(
            LinkHashingFixture::class,
            null,
            [
                $newsletter,
                new User(),
            ]
        );
    }

    /**
     * @return void
     */
    public function testConvertToAbsoluteHrefKeepsAbsoluteUrl(): void
    {
        $testUri = 'https://test.org';
        $uri = $this->subject->_call('convertToAbsoluteHref', $testUri);
        self::assertSame($testUri, $uri);
    }
}

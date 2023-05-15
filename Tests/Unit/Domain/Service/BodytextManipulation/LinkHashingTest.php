<?php

namespace In2code\Luxletter\Tests\Unit\Domain\Service\BodytextManipulation;

use In2code\Luxletter\Domain\Model\Configuration;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\BodytextManipulation\LinkHashingFixture;
use Nimut\TestingFramework\MockObject\AccessibleMockObjectInterface;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Luxletter\Domain\Service\BodytextManipulation\LinkHashing
 */
class LinkHashingTest extends UnitTestCase
{
    /**
     * @var AccessibleMockObjectInterface|MockObject|LinkHashingFixture
     * Todo: Add typehints to variable when PHP 7.4 is dropped
     */
    protected $generalValidatorMock;

    public function setUp(): void
    {
        parent::setUp();
        $configuration = new Configuration();
        $newsletter = new Newsletter();
        $newsletter->setConfiguration($configuration);
        $this->generalValidatorMock = $this->getAccessibleMock(
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
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getFilenameFromPathAndFilename
     */
    public function testGetFilenameFromPathAndFilename()
    {
        $testUri = 'https://test.org';
        $uri = $this->generalValidatorMock->_call('convertToAbsoluteHref', $testUri);
        self::assertSame($testUri, $uri);
    }
}

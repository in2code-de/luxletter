<?php
namespace In2code\Luxletter\Tests\Unit\Domain\Service;

use In2code\Luxletter\Domain\Model\Configuration;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\LinkHashingServiceFixture;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class FileUtilityTest
 * @coversDefaultClass \In2code\Luxletter\Domain\Service\LinkHashingService
 */
class LinkHashingServiceTest extends UnitTestCase
{
    /**
     * @var \In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\LinkHashingServiceFixture
     */
    protected $generalValidatorMock;

    /**
     * @return void
     */
    public function setUp()
    {
        $configuration = new Configuration();
        $newsletter = new Newsletter();
        $newsletter->setConfiguration($configuration);
        $this->generalValidatorMock = $this->getAccessibleMock(
            LinkHashingServiceFixture::class,
            ['dummy'],
            [
                $newsletter,
                new User()
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
        $this->assertSame($testUri, $uri);
    }
}

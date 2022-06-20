<?php
namespace In2code\Luxletter\Tests\Unit\Domain\Service\BodytextManipulation;

use In2code\Luxletter\Domain\Model\Configuration;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\BodytextManipulation\LinkHashingFixture;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class FileUtilityTest
 * @coversDefaultClass \In2code\Luxletter\Domain\Service\BodytextManipulation\LinkHashing
 */
class LinkHashingTest extends UnitTestCase
{
    /**
     * @var LinkHashingFixture
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
            LinkHashingFixture::class,
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

<?php
namespace In2code\Luxletter\Tests\Unit\Domain\Service\BodytextManipulation\ImageEmbedding;

use In2code\Luxletter\Domain\Service\BodytextManipulation\ImageEmbedding\Execution;
use In2code\Luxletter\Exception\ApiConnectionException;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\BodytextManipulation\ImageEmbedding\ExecutionFixture;
use In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\BodytextManipulation\ImageEmbedding\PreparationFixture;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;

/**
 * Class ExecutionTest
 * @coversDefaultClass Execution
 */
class ExecutionTest extends UnitTestCase
{
    /**
     * @var ExecutionFixture
     */
    protected $generalValidatorMock;

    /**
     * @var string[]
     */
    protected $bodytextExamples = [
        '<html>
            <body>
                <div>
                    <img src="https://via.placeholder.com/300/09f/fff.png" alt="image"/>
                    <h1>in2code</h1>
                </div>
            </body>
        </html>',
        '<html>
            <body>
                <div>
                    <img src="https://via.placeholder.com/300/09f/fff.png" alt="image"/>
                    <img src="https://via.placeholder.com/300.jpg" class="class name"/>
                    <img src="https://via.placeholder.com/250/09f/fff.png" alt="image"/>
                    <img src="https://via.placeholder.com/250.jpg" class="class name"/>
                    <img src="https://via.placeholder.com/200/09f/fff.png" alt="image"/>
                    <img src="https://via.placeholder.com/200.jpg" class="class name"/>
                    <img src="https://via.placeholder.com/150/09f/fff.png" alt="image"/>
                    <img src="https://via.placeholder.com/150.jpg" class="class name"/>
                    <img src="https://via.placeholder.com/100/09f/fff.png" alt="image"/>
                    <img src="https://via.placeholder.com/100.jpg" class="class name"/>
                    <h1>in2code</h1>
                </div>
            </body>
        </html>',
        '<html>
            <body>
                <div>
                    <img src="https://via.placeholder.com/200.jpg" class="class name"/>
                    <img src="https://via.placeholder.com/100.jpg" class="class name"/>
                    <img src="https://via.placeholder.com/200.jpg" class="class name"/>
                    <img src="https://via.placeholder.com/200.jpg" class="class name"/>
                    <h1>in2code</h1>
                </div>
            </body>
        </html>',
    ];

    protected $imagesExamples = [
        [
            'https://via.placeholder.com/300/09f/fff.png',
        ],
        [
            'https://via.placeholder.com/300/09f/fff.png',
            'https://via.placeholder.com/300.jpg',
            'https://via.placeholder.com/250/09f/fff.png',
            'https://via.placeholder.com/250.jpg',
            'https://via.placeholder.com/200/09f/fff.png',
            'https://via.placeholder.com/200.jpg',
            'https://via.placeholder.com/150/09f/fff.png',
            'https://via.placeholder.com/150.jpg',
            'https://via.placeholder.com/100/09f/fff.png',
            'https://via.placeholder.com/100.jpg',
        ],
        [
            'https://via.placeholder.com/200.jpg',
            'https://via.placeholder.com/100.jpg',
            'https://via.placeholder.com/200.jpg',
            'https://via.placeholder.com/200.jpg',
        ],
    ];

    protected $pathExamples = [
        [
            '/path/absolute/uploads/tx_luxletter/166e41227ca179735b3e29ee363c4d6810a767d77fb5d37ed18e1b064e56faaa.png',
            '/path/absolute/uploads/tx_luxletter/dab5e6deb01f5264f07143a3b02f5082d17c21fdf362d8228d85d3c0c4c3ac4c.jpg',
            '/path/absolute/uploads/tx_luxletter/166e41227ca179735b3e29ee363c4d6810a767d77fb5d37ed18e1b064e56faaa.png',
        ],
    ];

    /**
     * @return void
     */
    public function setUp()
    {
        $this->generalValidatorMock = $this->getAccessibleMock(ExecutionFixture::class, ['dummy']);
    }

    /**
     * @return void
     * @covers ::setBodytext
     */
    public function testSetBodytext(): void
    {
        $this->assertSame('', $this->generalValidatorMock->_get('content'));
        $this->assertNull($this->generalValidatorMock->_get('dom'));
        $this->generalValidatorMock->_call('setBodytext', $this->bodytextExamples[0]);
        $this->assertSame($this->bodytextExamples[0], $this->generalValidatorMock->_get('content'));
        $this->assertInstanceOf(\DomDocument::class, $this->generalValidatorMock->_get('dom'));
    }

    /**
     * @return void
     * @throws ApiConnectionException
     * @throws MisconfigurationException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @covers ::getImages
     */
    public function testGetImages(): void
    {
        // temporarily store images
        $preparationFixture = new PreparationFixture();
        $preparationFixture->storeImages($this->bodytextExamples[0]);

        // check for one image
        $this->generalValidatorMock->_call('setBodytext', $this->bodytextExamples[0]);
        $result1 = $this->generalValidatorMock->_call('getImages');
        $image1Cid = $this->generalValidatorMock->_call('getEmbedNameForPathAndFilename',
            $this->generalValidatorMock->_call('getNewImagePathAndFilename', $this->imagesExamples[0][0]));
        $this->assertArrayHasKey($image1Cid, $result1);
        $this->assertFileExists(current($result1));

        // check for ten images
        $preparationFixture->storeImages($this->bodytextExamples[1]);
        $this->generalValidatorMock->_call('setBodytext', $this->bodytextExamples[1]);
        $result2 = $this->generalValidatorMock->_call('getImages');
        $iteration = 0;
        foreach ($result2 as $name => $path) {
            $iteration++;
            $this->assertRegExp('~^name_[0-9a-f]{32}$~', $name);
        }
        $this->assertEquals(10, $iteration);

        // check for two images - no duplicates
        $preparationFixture->storeImages($this->bodytextExamples[2]);
        $this->generalValidatorMock->_call('setBodytext', $this->bodytextExamples[2]);
        $result3 = $this->generalValidatorMock->_call('getImages');
        $iteration = 0;
        foreach ($result3 as $name => $path) {
            $iteration++;
            $this->assertRegExp('~^name_[0-9a-f]{32}$~', $name);
        }
        $this->assertEquals(2, $iteration);
    }

    /**
     * @return void
     * @covers ::getRewrittenContent
     */
    public function testGetRewrittenContent(): void
    {
        $this->generalValidatorMock->_call('setBodytext', $this->bodytextExamples[1]);
        $content = $this->generalValidatorMock->_call('getRewrittenContent');
        $image1Cid = $this->generalValidatorMock->_call('getEmbedNameForPathAndFilename',
            $this->generalValidatorMock->_call('getNewImagePathAndFilename', $this->imagesExamples[1][0]));
        $image10Cid = $this->generalValidatorMock->_call('getEmbedNameForPathAndFilename',
            $this->generalValidatorMock->_call('getNewImagePathAndFilename', $this->imagesExamples[1][9]));
        $this->assertNotSame($image1Cid, $image10Cid);
        $this->assertNotFalse(stripos($content, 'cid:' . $image1Cid));
        $this->assertNotFalse(stripos($content, 'cid:' . $image10Cid));
    }

    /**
     * @return void
     * @covers ::getEmbedNameFromIterator
     */
    public function testGetEmbedNameForPathAndFilename(): void
    {
        $image1Cid = $this->generalValidatorMock->_call('getEmbedNameForPathAndFilename', $this->pathExamples[0][0]);
        $image2Cid = $this->generalValidatorMock->_call('getEmbedNameForPathAndFilename', $this->pathExamples[0][1]);
        $image3Cid = $this->generalValidatorMock->_call('getEmbedNameForPathAndFilename', $this->pathExamples[0][2]);
        $this->assertNotSame($image1Cid, $image2Cid);
        $this->assertSame($image1Cid, $image3Cid);
        $this->assertSame('name_0986b8209e4f2bd20600ceaa608aa66e', $image1Cid);
        $this->assertSame('name_a04943510563b9f18be90c3f38ecb806', $image2Cid);
        $this->assertSame('name_0986b8209e4f2bd20600ceaa608aa66e', $image3Cid);
    }

    /**
     * @return void
     * @covers ::checkInitialization
     */
    public function testCheckInitialization(): void
    {
        $this->expectExceptionCode(1637319117);
        $this->generalValidatorMock->_call('checkInitialization');

        $this->generalValidatorMock->_call('setBodytext', $this->bodytextExamples[0]);
        $this->generalValidatorMock->_call('checkInitialization');
    }
}

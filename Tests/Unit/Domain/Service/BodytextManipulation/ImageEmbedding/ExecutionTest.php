<?php

namespace In2code\Luxletter\Tests\Unit\Domain\Service\BodytextManipulation\ImageEmbedding;

use In2code\Luxletter\Domain\Service\BodytextManipulation\ImageEmbedding\Execution;
use In2code\Luxletter\Exception\ApiConnectionException;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\BodytextManipulation\ImageEmbedding\ExecutionFixture;
use In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\BodytextManipulation\ImageEmbedding\PreparationFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use UnexpectedValueException;

#[CoversClass(Execution::class)]
class ExecutionTest extends UnitTestCase
{
    protected AccessibleObjectInterface&ExecutionFixture $subject;

    protected array $bodytextExamples = [
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

    protected array $imagesExamples = [
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

    protected array $pathExamples = [
        [
            '/path/absolute/uploads/tx_luxletter/166e41227ca179735b3e29ee363c4d6810a767d77fb5d37ed18e1b064e56faaa.png',
            '/path/absolute/uploads/tx_luxletter/dab5e6deb01f5264f07143a3b02f5082d17c21fdf362d8228d85d3c0c4c3ac4c.jpg',
            '/path/absolute/uploads/tx_luxletter/166e41227ca179735b3e29ee363c4d6810a767d77fb5d37ed18e1b064e56faaa.png',
        ],
    ];

    public function setUp(): void
    {
        parent::setUp();
        $accessibleClassName = $this->buildAccessibleProxy(ExecutionFixture::class);
        $this->subject = new $accessibleClassName();
    }

    /**
     * @return void
     */
    public function testSetBodytext(): void
    {
        self::assertSame('', $this->subject->_get('content'));
        self::assertNull($this->subject->_get('dom'));
        $this->subject->_call('setBodytext', $this->bodytextExamples[0]);
        self::assertSame($this->bodytextExamples[0], $this->subject->_get('content'));
        self::assertInstanceOf(\DOMDocument::class, $this->subject->_get('dom'));
    }

    /**
     * @return void
     * @throws ApiConnectionException
     * @throws MisconfigurationException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function testGetImages(): void
    {
        // temporarily store images
        $preparationFixture = new PreparationFixture();
        $preparationFixture->storeImages($this->bodytextExamples[0]);

        // check for one image
        $this->subject->_call('setBodytext', $this->bodytextExamples[0]);
        $result1 = $this->subject->_call('getImages');
        $image1Cid = $this->subject->_call(
            'getEmbedNameForPathAndFilename',
            $this->subject->_call('getNewImagePathAndFilename', $this->imagesExamples[0][0])
        );
        self::assertArrayHasKey($image1Cid, $result1);
        self::assertFileExists(current($result1));

        // check for ten images
        $preparationFixture->storeImages($this->bodytextExamples[1]);
        $this->subject->_call('setBodytext', $this->bodytextExamples[1]);
        $result2 = $this->subject->_call('getImages');
        $iteration = 0;
        foreach ($result2 as $name => $path) {
            $iteration++;
            self::assertMatchesRegularExpression('~^name_[0-9a-f]{32}$~', $name);
        }
        self::assertEquals(10, $iteration);

        // check for two images - no duplicates
        $preparationFixture->storeImages($this->bodytextExamples[2]);
        $this->subject->_call('setBodytext', $this->bodytextExamples[2]);
        $result3 = $this->subject->_call('getImages');
        $iteration = 0;
        foreach ($result3 as $name => $path) {
            $iteration++;
            self::assertMatchesRegularExpression('~^name_[0-9a-f]{32}$~', $name);
        }
        self::assertEquals(2, $iteration);
    }

    /**
     * @return void
     */
    public function testGetRewrittenContent(): void
    {
        $this->subject->_call('setBodytext', $this->bodytextExamples[1]);
        $content = $this->subject->_call('getRewrittenContent');
        $image1Cid = $this->subject->_call(
            'getEmbedNameForPathAndFilename',
            $this->subject->_call('getNewImagePathAndFilename', $this->imagesExamples[1][0])
        );
        $image10Cid = $this->subject->_call(
            'getEmbedNameForPathAndFilename',
            $this->subject->_call('getNewImagePathAndFilename', $this->imagesExamples[1][9])
        );
        self::assertNotSame($image1Cid, $image10Cid);
        self::assertNotFalse(stripos($content, 'cid:' . $image1Cid));
        self::assertNotFalse(stripos($content, 'cid:' . $image10Cid));
    }

    /**
     * @return void
     */
    public function testGetEmbedNameForPathAndFilename(): void
    {
        $image1Cid = $this->subject->_call('getEmbedNameForPathAndFilename', $this->pathExamples[0][0]);
        $image2Cid = $this->subject->_call('getEmbedNameForPathAndFilename', $this->pathExamples[0][1]);
        $image3Cid = $this->subject->_call('getEmbedNameForPathAndFilename', $this->pathExamples[0][2]);
        self::assertNotSame($image1Cid, $image2Cid);
        self::assertSame($image1Cid, $image3Cid);
        self::assertSame('name_0986b8209e4f2bd20600ceaa608aa66e', $image1Cid);
        self::assertSame('name_a04943510563b9f18be90c3f38ecb806', $image2Cid);
        self::assertSame('name_0986b8209e4f2bd20600ceaa608aa66e', $image3Cid);
    }

    /**
     * @return void
     */
    public function testCheckInitializationThrowsExceptionWithoutBodytext(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionCode(1637319117);
        $this->subject->_call('checkInitialization');
    }

    /**
     * @return void
     */
    public function testCheckInitializationPassesWithBodytext(): void
    {
        $this->subject->_call('setBodytext', $this->bodytextExamples[0]);
        $this->subject->_call('checkInitialization');
        $this->addToAssertionCount(1);
    }
}

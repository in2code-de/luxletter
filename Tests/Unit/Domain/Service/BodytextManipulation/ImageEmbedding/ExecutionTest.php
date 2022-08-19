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
        $this->assertArrayHasKey('name_00000001', $result1);
        $this->assertTrue(file_exists(current($result1)));

        // check for ten images
        $preparationFixture->storeImages($this->bodytextExamples[1]);
        $this->generalValidatorMock->_call('setBodytext', $this->bodytextExamples[1]);
        $result2 = $this->generalValidatorMock->_call('getImages');
        $iteration = 0;
        foreach ($result2 as $name => $path) {
            $iteration++;
            $this->assertEmpty(preg_replace('~name_\d{8}~', '', $name));
        }
        $this->assertEquals(10, $iteration);
    }

    /**
     * @return void
     * @covers ::getRewrittenContent
     */
    public function testGetRewrittenContent(): void
    {
        $this->generalValidatorMock->_call('setBodytext', $this->bodytextExamples[1]);
        $content = $this->generalValidatorMock->_call('getRewrittenContent');
        $this->assertTrue(stristr($content, 'cid:name_00000001') !== false);
        $this->assertTrue(stristr($content, 'cid:name_00000010') !== false);
    }

    /**
     * @return void
     * @covers ::getEmbedNameFromIterator
     */
    public function testGetEmbedNameFromIterator(): void
    {
        $this->assertSame('name_00000001', $this->generalValidatorMock->_call('getEmbedNameFromIterator', 1));
        $this->assertSame('name_00000011', $this->generalValidatorMock->_call('getEmbedNameFromIterator', 11));
        $this->assertSame('name_00000456', $this->generalValidatorMock->_call('getEmbedNameFromIterator', 456));
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

<?php
namespace In2code\Luxletter\Tests\Unit\Utility;

use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\StringUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class FileUtilityTest
 * @coversDefaultClass \In2code\Luxletter\Utility\StringUtility
 */
class StringUtilityTest extends UnitTestCase
{
    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getFilenameFromPathAndFilename
     */
    public function testIsAbsoluteImageUrl(): void
    {
        $this->assertTrue(StringUtility::isAbsoluteImageUrl('https://www.in2code.de/image.png'));
        $this->assertTrue(StringUtility::isAbsoluteImageUrl('https://www.in2code.de/image.jpeg'));
        $this->assertTrue(StringUtility::isAbsoluteImageUrl('https://www.in2code.de/image.gif'));
        $this->assertTrue(StringUtility::isAbsoluteImageUrl('https://www.in2code.de/image.jpg'));
        $this->assertTrue(StringUtility::isAbsoluteImageUrl('https://www.in2code.de/image.webp'));
        $this->assertTrue(StringUtility::isAbsoluteImageUrl('https://www.in2code.de/image.svg'));
        $this->assertFalse(StringUtility::isAbsoluteImageUrl('https://www.in2code.de/image.svg/path'));
        $this->assertFalse(StringUtility::isAbsoluteImageUrl('https://www.in2code.de'));
        $this->assertFalse(StringUtility::isAbsoluteImageUrl('http://in2code.de/fileadmin/whitepaper.pdf'));
        $this->assertFalse(StringUtility::isAbsoluteImageUrl('/folder/page.jpg'));
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getFilenameFromPathAndFilename
     */
    public function testIsValidUrl(): void
    {
        $this->assertTrue(StringUtility::isValidUrl('https://www.in2code.de'));
        $this->assertTrue(StringUtility::isValidUrl('http://in2code.de/fileadmin/whitepaper.pdf'));
        $this->assertFalse(StringUtility::isValidUrl('abc'));
        $this->assertFalse(StringUtility::isValidUrl('/'));
        $this->assertFalse(StringUtility::isValidUrl('#'));
        $this->assertFalse(StringUtility::isValidUrl('/folder/page'));
        $this->assertFalse(StringUtility::isValidUrl('/folder/page.html'));
        $this->assertFalse(StringUtility::isValidUrl('undefined'));
    }

    /**
     * @return array
     */
    public function startsWithDataProvider(): array
    {
        return [
            [
                'Finisherx',
                'Finisher',
                true
            ],
            [
                'inisher',
                'Finisher',
                false
            ],
            [
                'abc',
                'a',
                true
            ],
            [
                'abc',
                'ab',
                true
            ],
            [
                'abc',
                'abc',
                true
            ],
        ];
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param bool $expectedResult
     * @return void
     * @dataProvider startsWithDataProvider
     * @test
     * @covers ::startsWith
     */
    public function testStartsWith($haystack, $needle, $expectedResult): void
    {
        $this->assertSame($expectedResult, StringUtility::startsWith($haystack, $needle));
    }

    /**
     * @return array
     */
    public function endsWithDataProvider(): array
    {
        return [
            [
                'xFinisher',
                'Finisher',
                true
            ],
            [
                'inisher',
                'Finisher',
                false
            ],
            [
                'abc',
                'c',
                true
            ],
            [
                'abc',
                'bc',
                true
            ],
            [
                'abc',
                'abc',
                true
            ],
            [
                '/test//',
                '/',
                true
            ],
            [
                '/test//x',
                '/',
                false
            ],
        ];
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param bool $expectedResult
     * @return void
     * @dataProvider endsWithDataProvider
     * @test
     * @covers ::endsWith
     */
    public function testEndsWith($haystack, $needle, $expectedResult): void
    {
        $this->assertSame($expectedResult, StringUtility::endsWith($haystack, $needle));
    }

    /**
     * @return void
     * @throws MisconfigurationException
     * @throws Exception
     * @covers ::getHashFromArguments
     */
    public function testGetHashFromArguments(): void
    {
        $arguments = [
            'foo' => 'bar',
            'bar' => 'foo'
        ];
        $hash = StringUtility::getHashFromArguments($arguments, false);
        $this->assertSame('852efa41125da97602459ac029689f486999f5d10ba84ab0432a144e7bb3abab', $hash);
    }
}

<?php

namespace In2code\Luxletter\Tests\Unit\Utility;

use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Tests\Unit\Fixtures\EncryptionKeyTrait;
use In2code\Luxletter\Utility\StringUtility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(StringUtility::class)]
class StringUtilityTest extends UnitTestCase
{
    use EncryptionKeyTrait;

    /**
     * @return void
     */
    public function testIsAbsoluteImageUrl(): void
    {
        self::assertTrue(StringUtility::isAbsoluteImageUrl('https://www.in2code.de/image.png'));
        self::assertTrue(StringUtility::isAbsoluteImageUrl('https://www.in2code.de/image.jpeg'));
        self::assertTrue(StringUtility::isAbsoluteImageUrl('https://www.in2code.de/image.gif'));
        self::assertTrue(StringUtility::isAbsoluteImageUrl('https://www.in2code.de/image.jpg'));
        self::assertTrue(StringUtility::isAbsoluteImageUrl('https://www.in2code.de/image.webp'));
        self::assertTrue(StringUtility::isAbsoluteImageUrl('https://www.in2code.de/image.svg'));
        self::assertFalse(StringUtility::isAbsoluteImageUrl('https://www.in2code.de/image.svg/path'));
        self::assertFalse(StringUtility::isAbsoluteImageUrl('https://www.in2code.de'));
        self::assertFalse(StringUtility::isAbsoluteImageUrl('http://in2code.de/fileadmin/whitepaper.pdf'));
        self::assertFalse(StringUtility::isAbsoluteImageUrl('/folder/page.jpg'));
    }

    /**
     * @return void
     */
    public function testIsValidUrl(): void
    {
        self::assertTrue(StringUtility::isValidUrl('https://www.in2code.de'));
        self::assertTrue(StringUtility::isValidUrl('http://in2code.de/fileadmin/whitepaper.pdf'));
        self::assertFalse(StringUtility::isValidUrl('abc'));
        self::assertFalse(StringUtility::isValidUrl('/'));
        self::assertFalse(StringUtility::isValidUrl('#'));
        self::assertFalse(StringUtility::isValidUrl('/folder/page'));
        self::assertFalse(StringUtility::isValidUrl('/folder/page.html'));
        self::assertFalse(StringUtility::isValidUrl('undefined'));
    }

    public static function startsWithDataProvider(): array
    {
        return [
            [
                'Finisherx',
                'Finisher',
                true,
            ],
            [
                'inisher',
                'Finisher',
                false,
            ],
            [
                'abc',
                'a',
                true,
            ],
            [
                'abc',
                'ab',
                true,
            ],
            [
                'abc',
                'abc',
                true,
            ],
        ];
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param bool $expectedResult
     * @return void
     */
    #[DataProvider('startsWithDataProvider')]
    public function testStartsWith($haystack, $needle, $expectedResult): void
    {
        self::assertSame($expectedResult, StringUtility::startsWith($haystack, $needle));
    }

    public static function endsWithDataProvider(): array
    {
        return [
            [
                'xFinisher',
                'Finisher',
                true,
            ],
            [
                'inisher',
                'Finisher',
                false,
            ],
            [
                'abc',
                'c',
                true,
            ],
            [
                'abc',
                'bc',
                true,
            ],
            [
                'abc',
                'abc',
                true,
            ],
            [
                '/test//',
                '/',
                true,
            ],
            [
                '/test//x',
                '/',
                false,
            ],
        ];
    }

    /**
     * @param string $haystack
     * @param string $needle
     * @param bool $expectedResult
     * @return void
     */
    #[DataProvider('endsWithDataProvider')]
    public function testEndsWith($haystack, $needle, $expectedResult): void
    {
        self::assertSame($expectedResult, StringUtility::endsWith($haystack, $needle));
    }

    /**
     * @return void
     * @throws MisconfigurationException
     */
    public function testGetHashFromArguments(): void
    {
        $arguments = [
            'foo' => 'bar',
            'bar' => 'foo',
        ];
        $hash = StringUtility::getHashFromArguments($arguments, false);
        self::assertSame('852efa41125da97602459ac029689f486999f5d10ba84ab0432a144e7bb3abab', $hash);
    }

    /**
     * @return void
     * @throws MisconfigurationException
     */
    public function testGetHashFromArgumentsAddsEncryptionKey(): void
    {
        $this->addEncryptionKey('secret');
        $arguments = [
            'foo' => 'bar',
            'bar' => 'foo',
        ];
        self::assertSame(hash('sha256', 'bar/foo/secret'), StringUtility::getHashFromArguments($arguments));
    }

    /**
     * @return void
     */
    public function testGetHashFromArgumentsThrowsExceptionWithoutEncryptionKey(): void
    {
        $this->addEncryptionKey('');

        $this->expectException(MisconfigurationException::class);
        $this->expectExceptionCode(1562069158);
        StringUtility::getHashFromArguments(['foo']);
    }

    public static function removeStringPostfixDataProvider(): array
    {
        return [
            'controller name' => ['NewsletterController', 'Controller', 'Newsletter'],
            'action name' => ['dashboardAction', 'Action', 'dashboard'],
            'only at the end' => ['ActionlistAction', 'Action', 'Actionlist'],
            'postfix not given' => ['Newsletter', 'Controller', 'Newsletter'],
        ];
    }

    /**
     * @param string $string
     * @param string $postfix
     * @param string $expected
     * @return void
     */
    #[DataProvider('removeStringPostfixDataProvider')]
    public function testRemoveStringPostfix(string $string, string $postfix, string $expected): void
    {
        self::assertSame($expected, StringUtility::removeStringPostfix($string, $postfix));
    }
}

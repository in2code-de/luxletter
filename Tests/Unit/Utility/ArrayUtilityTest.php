<?php

namespace In2code\Luxletter\Tests\Unit\Utility;

use In2code\Luxletter\Utility\ArrayUtility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ArrayUtility::class)]
class ArrayUtilityTest extends UnitTestCase
{
    public static function convertToIntegerArrayDataProvider(): array
    {
        return [
            [
                [],
                [],
            ],
            [
                [1, 2, 3, 4],
                [1, 2, 3, 4],
            ],
            [
                ['1', 2, '3', 4],
                [1, 2, 3, 4],
            ],
            [
                ['0', '3'],
                [0, 3],
            ],
        ];
    }

    /**
     * @param array $actual
     * @param array $expected
     * @return void
     */
    #[DataProvider('convertToIntegerArrayDataProvider')]
    public function testConvertToIntegerArray(array $actual, array $expected): void
    {
        self::assertSame($expected, ArrayUtility::convertToIntegerArray($actual));
    }

    public static function convertArrayToIntegerListDataProvider(): array
    {
        return [
            [
                [],
                '',
            ],
            [
                [1, 2, 3, 4],
                '1,2,3,4',
            ],
            [
                ['1a', 2, 'a3', 4],
                '1,2,0,4',
            ],
            [
                ['0', '3'],
                '0,3',
            ],
            [
                [01, 2],
                '1,2',
            ],
        ];
    }

    /**
     * @param array $actual
     * @param string $expected
     * @return void
     */
    #[DataProvider('convertArrayToIntegerListDataProvider')]
    public function testConvertArrayToIntegerList(array $actual, string $expected): void
    {
        self::assertSame($expected, ArrayUtility::convertArrayToIntegerList($actual));
    }
}

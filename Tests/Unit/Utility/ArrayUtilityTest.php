<?php

namespace In2code\Luxletter\Tests\Unit\Utility;

use In2code\Luxletter\Utility\ArrayUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Luxletter\Utility\ArrayUtility
 */
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
     * @dataProvider convertToIntegerArrayDataProvider
     * @test
     * @covers ::convertToIntegerArray
     */
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
     * @dataProvider convertArrayToIntegerListDataProvider
     * @test
     * @covers ::convertArrayToIntegerList
     */
    public function testConvertArrayToIntegerList(array $actual, string $expected): void
    {
        self::assertSame($expected, ArrayUtility::convertArrayToIntegerList($actual));
    }
}

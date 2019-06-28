<?php
namespace In2code\Luxletter\Tests\Unit\Utility;

use In2code\Luxletter\Utility\StringUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;

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
    public function testIsValidUrl()
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
}

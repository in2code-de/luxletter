<?php
namespace In2code\Luxletter\Tests\Unit\Utility;

use In2code\Luxletter\Utility\FileUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class FileUtilityTest
 * @coversDefaultClass \In2code\Luxletter\Utility\FileUtility
 */
class FileUtilityTest extends UnitTestCase
{
    /**
     * @return void
     * @covers ::getExtensionFromPathAndFilename
     */
    public function testGetExtensionFromPathAndFilename(): void
    {
        $this->assertSame('jpeg', FileUtility::getExtensionFromPathAndFilename('filename.jpeg'));
        $this->assertSame('webp', FileUtility::getExtensionFromPathAndFilename('fileadmin/image.webp'));
        $this->assertSame('zip', FileUtility::getExtensionFromPathAndFilename('/var/www/file.jpg.zip'));
    }
}

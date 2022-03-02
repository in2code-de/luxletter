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

    /**
     * @return array
     */
    public function addLanguageIsocodeToFilenameDataProvider(): array
    {
        return [
            [
                '/var/www/filename.test.html',
                'en',
                '/var/www/filename.test_en.html'
            ],
            [
                'image.jpg',
                'de',
                'image_de.jpg'
            ],
            [
                'fileadmin/folder/file.gif',
                'fr',
                'fileadmin/folder/file_fr.gif'
            ],
        ];
    }

    /**
     * @param string $filename
     * @param string $isocode
     * @param string $expected
     * @return void
     * @dataProvider addLanguageIsocodeToFilenameDataProvider
     * @covers ::addLanguageIsocodeToFilename
     */
    public function testAddLanguageIsocodeToFilename(string $filename, string $isocode, string $expected): void
    {
        $this->assertSame($expected, FileUtility::addLanguageIsocodeToFilename($filename, $isocode));
    }
}

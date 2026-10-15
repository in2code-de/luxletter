<?php

namespace In2code\Luxletter\Tests\Unit\Utility;

use In2code\Luxletter\Utility\FileUtility;
use PHPUnit\Framework\Attributes\CoversClass;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(FileUtility::class)]
class FileUtilityTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testGetExtensionFromPathAndFilename(): void
    {
        self::assertSame('jpeg', FileUtility::getExtensionFromPathAndFilename('filename.jpeg'));
        self::assertSame('webp', FileUtility::getExtensionFromPathAndFilename('fileadmin/image.webp'));
        self::assertSame('zip', FileUtility::getExtensionFromPathAndFilename('/var/www/file.jpg.zip'));
    }
}

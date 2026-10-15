<?php

namespace In2code\Luxletter\Tests\Unit\Utility;

use In2code\Luxletter\Utility\FileUtility;
use PHPUnit\Framework\Attributes\CoversClass;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(FileUtility::class)]
class FileUtilityTest extends UnitTestCase
{
    protected string $testFolder = '';

    public function setUp(): void
    {
        parent::setUp();
        $this->testFolder = Environment::getVarPath() . '/tests/' . $this->getUniqueId('luxletter_');
    }

    /**
     * @return void
     */
    public function testGetExtensionFromPathAndFilename(): void
    {
        self::assertSame('jpeg', FileUtility::getExtensionFromPathAndFilename('filename.jpeg'));
        self::assertSame('webp', FileUtility::getExtensionFromPathAndFilename('fileadmin/image.webp'));
        self::assertSame('zip', FileUtility::getExtensionFromPathAndFilename('/var/www/file.jpg.zip'));
        self::assertSame('', FileUtility::getExtensionFromPathAndFilename('/var/www/file'));
        self::assertSame('', FileUtility::getExtensionFromPathAndFilename(''));
    }

    /**
     * @return void
     */
    public function testCreateFolderIfNotExistsCreatesNestedFolders(): void
    {
        $this->testFilesToDelete[] = $this->testFolder;
        $path = $this->testFolder . '/sub/folder';

        FileUtility::createFolderIfNotExists($path);
        FileUtility::createFolderIfNotExists($path);

        self::assertDirectoryExists($path);
    }

    /**
     * @return void
     */
    public function testCreatePermissionDeniedHtaccessFileIfNotExistsDeniesAccess(): void
    {
        $this->testFilesToDelete[] = $this->testFolder;
        FileUtility::createFolderIfNotExists($this->testFolder);
        $pathAndFilename = $this->testFolder . '/.htaccess';

        FileUtility::createPermissionDeniedHtaccessFileIfNotExists($pathAndFilename);

        self::assertFileExists($pathAndFilename);
        self::assertStringContainsString('Require all denied', file_get_contents($pathAndFilename));
    }

    /**
     * @return void
     */
    public function testCreatePermissionDeniedHtaccessFileIfNotExistsKeepsExistingFile(): void
    {
        $this->testFilesToDelete[] = $this->testFolder;
        FileUtility::createFolderIfNotExists($this->testFolder);
        $pathAndFilename = $this->testFolder . '/.htaccess';
        file_put_contents($pathAndFilename, 'existing');

        FileUtility::createPermissionDeniedHtaccessFileIfNotExists($pathAndFilename);

        self::assertSame('existing', file_get_contents($pathAndFilename));
    }
}

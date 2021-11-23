<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Utility;

use Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UnexpectedValueException;

/**
 * Class FileUtility
 */
class FileUtility
{
    /**
     * @param string $pathAndFilename
     * @return string
     */
    public static function getExtensionFromPathAndFilename(string $pathAndFilename): string
    {
        $parts = pathinfo($pathAndFilename);
        if (isset($parts['extension'])) {
            return $parts['extension'];
        }
        return '';
    }

    /**
     * @param string $path absolute path
     * @return void
     */
    public static function createFolderIfNotExists(string $path): void
    {
        if (!is_dir($path)) {
            try {
                GeneralUtility::mkdir_deep($path);
            } catch (Exception $exception) {
                throw new UnexpectedValueException('Folder ' . $path . ' could not be created', 1637265764);
            }
        }
    }

    /**
     * @param string $pathAndFilename
     * @return void
     */
    public static function createPermissionDeniedHtaccessFileIfNotExists(string $pathAndFilename): void
    {
        $fileContent = '<IfModule !mod_authz_core.c>
	Order allow,deny
	Deny from all
	Satisfy All
</IfModule>

<IfModule mod_authz_core.c>
	Require all denied
</IfModule>
        ';
        self::createFileIfNotExists($fileContent, $pathAndFilename);
    }

    /**
     * @param string $content
     * @param string $pathAndFilename absolute path and filename "/var/www/file.txt"
     * @return void
     */
    protected static function createFileIfNotExists(string $content, string $pathAndFilename): void
    {
        if (!is_file($pathAndFilename)) {
            try {
                GeneralUtility::writeFile($pathAndFilename, $content);
            } catch (Exception $exception) {
                throw new UnexpectedValueException('File ' . $pathAndFilename . ' could not be created', 1637328181);
            }
        }
    }
}

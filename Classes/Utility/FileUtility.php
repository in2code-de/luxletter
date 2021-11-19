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
}

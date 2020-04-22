<?php
declare(strict_types=1);
namespace In2code\Luxletter\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class TemplateUtility
 */
class TemplateUtility
{

    /**
     * Because templateRootPaths can be extend from third party extensions, it's not sure which file will be used in
     * the end. This function detects the relevant file and give it back (e.g. for own parsing)
     *
     * @param string $templateName e.g. "Mail/NewsletterContainer.html"
     * @return string
     * @throws InvalidConfigurationTypeException
     */
    public static function getExistingFilePathOfTemplateFileByName(string $templateName): string
    {
        $templateRootPaths = ConfigurationUtility::getExtensionSettings()['view']['templateRootPaths'];
        krsort($templateRootPaths);
        foreach ($templateRootPaths as $rootPath) {
            $pathAndFilename = GeneralUtility::getFileAbsFileName($rootPath . $templateName);
            if (file_exists($pathAndFilename)) {
                return $pathAndFilename;
            }
        }
        return '';
    }
}

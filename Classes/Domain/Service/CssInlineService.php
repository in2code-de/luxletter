<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Utility\ConfigurationUtility;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * CssInlineService
 */
class CssInlineService
{
    /**
     * @param string $content
     * @return string
     * @throws InvalidConfigurationTypeException
     */
    public function addInlineCss(string $content): string
    {
        $configuration = ConfigurationUtility::getExtensionSettings();
        if (!empty($configuration['settings']['addInlineCss'])) {
            $cssToInline = GeneralUtility::makeInstance(CssToInlineStyles::class);
            $files = $configuration['settings']['addInlineCss'];
            $files = array_reverse($files);
            foreach ($files as $path) {
                $file = GeneralUtility::getFileAbsFileName($path);
                if (file_exists($file)) {
                    $content = $cssToInline->convert($content, file_get_contents($file));
                }
            }
        }
        return $content;
    }
}

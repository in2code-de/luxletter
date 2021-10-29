<?php

declare(strict_types = 1);

namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Exception\UnvalidFilenameException;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * LayoutService
 */
class LayoutService
{
    /**
     * Example return values:
     *  [
     *      'File1.html' => 'label',
     *      'File2.html' => 'label2',
     *  ]
     *
     * @return array
     * @throws InvalidConfigurationTypeException
     */
    public function getLayouts(): array
    {
        $containers = $this->getLayoutConfiguration();
        $layouts = [];
        foreach ($containers as $container) {
            if (!empty($container['label']) && !empty($container['fileName'])) {
                $label = $container['label'];
                if (StringUtility::startsWith($label, 'LLL:')) {
                    $label = ObjectUtility::getLanguageService()->sL($label);
                }
                $layouts[$container['fileName']] = $label;
            }
        }
        return $layouts;
    }

    /**
     * @param string $layout
     * @return string Relative path and filename like "EXT:sitepackage/../MailContainer.html"
     * @throws InvalidConfigurationTypeException
     * @throws UnvalidFilenameException
     * @throws MisconfigurationException
     */
    public function getPathAndFilenameFromLayout(string $layout): string
    {
        $this->checkForValidFilename($layout);
        if (is_file(GeneralUtility::getFileAbsFileName($this->getLayoutPath() . $layout)) === false) {
            throw new MisconfigurationException(
                'Could not read template file with given path and filename',
                1635495052
            );
        }
        return $this->getLayoutPath() . $layout;
    }

    /**
     * @param string $filename
     * @return void
     * @throws UnvalidFilenameException
     * @throws InvalidConfigurationTypeException
     */
    protected function checkForValidFilename(string $filename): void
    {
        $containers = $this->getLayoutConfiguration();
        foreach ($containers as $container) {
            if ($filename === $container['fileName']) {
                return;
            }
        }
        throw new UnvalidFilenameException(
            'Given filename (' . htmlspecialchars($filename) . ') is invalid',
            1635492796
        );
    }

    /**
     * Get layout configuration from TypoScript setup
     *  plugin {
     *      tx_luxletter_fe {
     *          settings {
     *              containerHtml {
     *                  path = EXT:luxletter/Resources/Private/Templates/Mail/
     *                  options {
     *                      1 {
     *                          label = LLL:EXT:path/locallang_db.xlf:newsletter.layouts.1
     *                          fileName = NewsletterContainer.html
     *                      }
     *                      2 {
     *                          label = C2
     *                          fileName = NewsletterContainer2.html
     *                      }
     *                  }
     *              }
     *          }
     *      }
     *  }
     *
     * @return array
     * @throws InvalidConfigurationTypeException
     */
    protected function getLayoutConfiguration(): array
    {
        $configuration = ConfigurationUtility::getExtensionSettings();
        if (!empty($configuration['settings']['containerHtml']['options'])) {
            return $configuration['settings']['containerHtml']['options'];
        }
        return [];
    }

    /**
     * @return string
     * @throws InvalidConfigurationTypeException
     * @throws MisconfigurationException
     */
    protected function getLayoutPath(): string
    {
        $configuration = ConfigurationUtility::getExtensionSettings();
        if (!empty($configuration['settings']['containerHtml']['path'])) {
            $path = $configuration['settings']['containerHtml']['path'];
            if (StringUtility::endsWith($path, '/') === false) {
                $path .= '/';
            }
            return $path;
        }
        throw new MisconfigurationException('No template path given in settings.containerHtml.path', 1635494884);
    }
}

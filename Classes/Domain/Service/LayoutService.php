<?php

declare(strict_types=1);

namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Domain\Repository\LanguageRepository;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Exception\RecordInDatabaseNotFoundException;
use In2code\Luxletter\Exception\UnvalidFilenameException;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

class LayoutService
{
    /**
     * Get layouts to fill a select field in new form
     *
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
     * @param int $language
     * @param string $origin
     * @return string Relative path and filename like "EXT:sitepackage/../MailContainer.html"
     * @throws InvalidConfigurationTypeException
     * @throws MisconfigurationException
     * @throws RecordInDatabaseNotFoundException
     * @throws UnvalidFilenameException
     */
    public function getPathAndFilenameFromLayout(string $layout, int $language, string $origin): string
    {
        $this->checkForValidFilename($layout);
        $pathAndFilename = '';
        if ($language > 0) {
            $pathAndFilename = $this->getPathAndFilenameFromLayoutForSpecificLanguage($layout, $language, $origin);
        }
        if ($pathAndFilename === '') {
            $pathAndFilename = $this->getPathAndFilenameFromLayoutForDefaultLanguage($layout);
        }
        return $pathAndFilename;
    }

    /**
     * @param string $layout
     * @return string
     * @throws InvalidConfigurationTypeException
     * @throws MisconfigurationException
     */
    protected function getPathAndFilenameFromLayoutForDefaultLanguage(string $layout): string
    {
        $filename = $this->getLayoutPath() . $layout . '.html';
        if (is_file(GeneralUtility::getFileAbsFileName($filename)) === false) {
            throw new MisconfigurationException(
                'Could not read template file with given path and filename',
                1635495052
            );
        }
        return $filename;
    }

    /**
     * @param string $layout
     * @param int $language
     * @param string $origin
     * @return string
     * @throws InvalidConfigurationTypeException
     * @throws MisconfigurationException
     */
    protected function getPathAndFilenameFromLayoutForSpecificLanguage(
        string $layout,
        int $language,
        string $origin
    ): string {
        $languageRepository = GeneralUtility::makeInstance(LanguageRepository::class);
        $isocode = $languageRepository->getIsocodeFromIdentifier($language, $origin);
        $filename = $this->getLayoutPath() . $layout . '_' . $isocode . '.html';
        if (is_file(GeneralUtility::getFileAbsFileName($filename))) {
            return $filename;
        }
        return '';
    }

    /**
     * Check if given filename
     * - Has no slashes (to prevent ../../anything.html)
     * - Ends not with a ".html"
     * - And is allowed by TypoScript configuration
     *
     * @param string $filename
     * @return void
     * @throws UnvalidFilenameException
     * @throws InvalidConfigurationTypeException
     */
    protected function checkForValidFilename(string $filename): void
    {
        if (stristr($filename, '/') !== false) {
            throw new UnvalidFilenameException(
                'Given filename (' . htmlspecialchars($filename) . ') contains invalid characters',
                1635497109
            );
        }
        if (StringUtility::endsWith($filename, '.html') === true) {
            throw new UnvalidFilenameException(
                'Given filename (' . htmlspecialchars($filename) . ') must NOT end with .html',
                1646381056
            );
        }
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
     *      tx_luxletter {
     *          settings {
     *              containerHtml {
     *                  path = EXT:luxletter/Resources/Private/Templates/Mail/
     *                  options {
     *                      1 {
     *                          # "NewsletterContainer" means:
     *                          # "NewsletterContainer.html" in default language or
     *                          # "NewsletterContainer_de.html" in german language and so on...
     *                          fileName = NewsletterContainer
     *                          label = LLL:EXT:path/locallang_db.xlf:newsletter.layouts.1
     *                      }
     *                      2 {
     *                          fileName = NewsletterContainer2
     *                          label = C2
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

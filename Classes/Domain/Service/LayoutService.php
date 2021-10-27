<?php

declare(strict_types=1);

namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Exception\UnvalidFilenameException;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use In2code\Luxletter\Utility\StringUtility;
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
     * @param string $filename
     * @return void
     * @throws UnvalidFilenameException
     * @throws InvalidConfigurationTypeException
     */
    public function checkForValidFilename(string $filename): void
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
}

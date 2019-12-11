<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository\Configuration;

use In2code\Luxletter\Domain\Model\Configuration\Configuration;
use In2code\Luxletter\Utility\ConfigurationUtility;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

class ConfigurationRepository implements SingletonInterface
{

    public const CONFIGURATION_FILE_SUFFIX = '.luxletter.yml';

    /**
     * @var string
     */
    protected $yamlFolder;

    public function __construct()
    {
        $this->yamlFolder = ConfigurationUtility::getYamlFolder();
    }

    public function findAll($prependDefault = true): array
    {
        $identifiers = $this->readIdentifiers();
        if ($prependDefault === true || count($identifiers) === 0) {
            array_unshift($identifiers, '');
        }
        return array_map([$this, 'mapIdentifierToConfigurationObject'], $identifiers);
    }

    protected function readIdentifiers(): array
    {
        $finder = new Finder();
        try {
            $finder->files()->depth(0)->name('*' . self::CONFIGURATION_FILE_SUFFIX)->in($this->yamlFolder);
            $finder = iterator_to_array($finder, false);
        } catch (\InvalidArgumentException $e) {
            // Directory $this->configPath does not exist yet
            $finder = [];
        }
        return array_map(static function (SplFileInfo $fileInfo) {
            return substr($fileInfo->getFilename(), 0, -strlen(self::CONFIGURATION_FILE_SUFFIX));
        }, $finder);
    }

    /**
     * @param string|null $identifier
     * @return Configuration
     * @throws FileDoesNotExistException
     */
    public function findOneByIdentifier(?string $identifier = null): Configuration
    {
        return $this->mapIdentifierToConfigurationObject($identifier ?? '');
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

    /**
     * @param string $identifier
     * @return array
     * @throws FileDoesNotExistException
     */
    protected function getConfigurationForIdentifier(string $identifier = ''): array
    {
        if ($identifier === '') {
            return [
                'title' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:tx_luxletter_domain_model_newsletter.configuration.__default.title',
                'description' => 'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:tx_luxletter_domain_model_newsletter.configuration.__default.description',
            ];
        }
        $configurationFile = $this->yamlFolder . $identifier . self::CONFIGURATION_FILE_SUFFIX;
        if (!is_readable($configurationFile)) {
            throw new FileDoesNotExistException(sprintf('Can‘t read configuration file %s', $configurationFile), 1576010961);
        }
        $loader = GeneralUtility::makeInstance(YamlFileLoader::class);
        return $loader->load(GeneralUtility::fixWindowsFilePath($configurationFile),
            YamlFileLoader::PROCESS_IMPORTS | YamlFileLoader::PROCESS_PLACEHOLDERS);
    }

    /**
     * @param string $identifier
     * @return Configuration
     * @throws FileDoesNotExistException
     */
    protected function mapIdentifierToConfigurationObject(string $identifier = ''): Configuration
    {
        $configuration = $this->getConfigurationForIdentifier($identifier ?? '');
        return GeneralUtility::makeInstance(
            Configuration::class,
            $identifier ?? '',
            $configuration['config'] ?? [],
            $this->getLanguageService()->sL($configuration['title']),
            $this->getLanguageService()->sL($configuration['description'])
        );
    }

}
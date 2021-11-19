<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Service\BodytextManipulation\ImageEmbedding;

use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\FileUtility;
use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UnexpectedValueException;

/**
 * Class AbstractEmbedding
 */
abstract class AbstractEmbedding
{
    /**
     * @var string
     */
    protected $imageFolder = 'uploads/tx_luxletter/';

    /**
     * @param string $imageUrl
     * @return string
     * @throws MisconfigurationException
     */
    protected function getNewImagePathAndFilename(string $imageUrl): string
    {
        return GeneralUtility::getFileAbsFileName($this->imageFolder)
            . $this->getHashedFilename($imageUrl) . '.'
            . FileUtility::getExtensionFromPathAndFilename($imageUrl);
    }

    /**
     * @param string $url
     * @return string
     * @throws MisconfigurationException
     */
    protected function getHashedFilename(string $url): string
    {
        if (StringUtility::isAbsoluteImageUrl($url) === false) {
            throw new UnexpectedValueException('Hashed filename can only be created from absolute image', 1637266454);
        }
        return hash('sha256', $url . ConfigurationUtility::getEncryptionKey());
    }

    /**
     * @return bool
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function isActive(): bool
    {
        return ConfigurationUtility::isImageEmbeddingActivated();
    }
}

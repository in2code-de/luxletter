<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Service\BodytextManipulation;

use DOMDocument;
use DOMElement;
use In2code\Luxletter\Exception\ApiConnectionException;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UnexpectedValueException;

/**
 * Class ImageEmbedding
 */
class ImageEmbedding implements SingletonInterface
{
    /**
     * @var string
     */
    protected $content = '';

    /**
     * @var DOMDocument
     */
    protected $dom = null;

    /**
     * @var array|null
     */
    protected $images = [];

    /**
     * @param string $content
     * @return $this
     */
    public function setBodytext(string $content): self
    {
        $this->content = $content;
        $this->dom = new DOMDocument;
        @$this->dom->loadHTML($this->content);
        return $this;
    }

    /**
     * Get bodytext of a mail and rewrite src to (e.g.) "cig:image-0"
     *
     * Example return value:
     *  [
     *      'image-0' => 'contentofimage1',
     *      'image-1' => 'contentofimage2',
     *  ]
     *
     * @return array
     * @throws ApiConnectionException
     */
    public function getImages(): array
    {
        $this->checkInitialization();

        if ($this->images === []) {
            $images = [];
            $imageTags = $this->dom->getElementsByTagName('img');
            /** @var DOMElement $imageTag */
            foreach ($imageTags as $imageTag) {
                $src = $imageTag->getAttribute('src');
                if (StringUtility::isValidUrl($src)) {
                    $iterator = count($images);
                    $images['image-' . $iterator] = $this->getImageContent($src);
                }
            }
            $this->images = $images;
        }
        return $this->images;
    }

    /**
     * Rewrite src to (e.g.) "cig:image-0"
     *
     * @return string
     */
    public function getRewrittenContent(): string
    {
        $this->checkInitialization();

        $iterator = 0;
        $imageTags = $this->dom->getElementsByTagName('img');
        /** @var DOMElement $imageTag */
        foreach ($imageTags as $imageTag) {
            $src = $imageTag->getAttribute('src');
            if (StringUtility::isValidUrl($src)) {
                $imageTag->setAttribute('src', 'cid:image-' . $iterator);
                $iterator++;
            }
        }
        return $this->dom->saveHTML();
    }

    /**
     * @param string $url
     * @return string
     * @throws ApiConnectionException
     */
    protected function getImageContent(string $url): string
    {
        try {
            $requestFactory = GeneralUtility::makeInstance(RequestFactory::class);
            $response = $requestFactory->request($url);
            if ($response->getStatusCode() === 200) {
                $content = $response->getBody()->getContents();
            } else {
                throw new ApiConnectionException('Image could not be fetched from ' . $url, 1637265921);
            }
        } catch (\Exception $exception) {
            throw new ApiConnectionException($exception->getMessage(), 1637265924);
        }
        return $content;
    }

    /**
     * @return void
     */
    protected function checkInitialization(): void
    {
        if ($this->content === '') {
            throw new UnexpectedValueException('No bodytext given for image embedding', 1637319117);
        }
        if ($this->dom === null) {
            throw new UnexpectedValueException('Dom property not initialized', 1637319084);
        }
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

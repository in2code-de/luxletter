<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Service\BodytextManipulation\ImageEmbedding;

use DOMDocument;
use DOMElement;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\FileUtility;
use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Core\SingletonInterface;
use UnexpectedValueException;

/**
 * Class Execution
 * To convert images in newletter bodytext
 */
class Execution extends AbstractEmbedding implements SingletonInterface
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
     *      'imagehash1' => '/var/www/imagehash1.jpg',
     *      'imagehash2' => '/var/www/imagehash2.jpg',
     *  ]
     *
     * @return array
     * @throws MisconfigurationException
     */
    public function getImages(): array
    {
        $this->checkInitialization();

        $images = [];
        $imageTags = $this->dom->getElementsByTagName('img');
        /** @var DOMElement $imageTag */
        foreach ($imageTags as $imageTag) {
            $src = $imageTag->getAttribute('src');
            if (StringUtility::isAbsoluteImageUrl($src)) {
                $pathAndFilename = $this->getNewImagePathAndFilename($src);
                if (file_exists($pathAndFilename)) {
                    $images[FileUtility::getFilenameFromPathAndFilename($pathAndFilename)] = $pathAndFilename;
                }
            }
        }
        return $images;
    }

    /**
     * Rewrite src to "cid:imagehash1"
     *
     * @return string
     * @throws MisconfigurationException
     */
    public function getRewrittenContent(): string
    {
        $this->checkInitialization();

        $imageTags = $this->dom->getElementsByTagName('img');
        /** @var DOMElement $imageTag */
        foreach ($imageTags as $imageTag) {
            $src = $imageTag->getAttribute('src');
            if (StringUtility::isAbsoluteImageUrl($src)) {
                $pathAndFilename = $this->getNewImagePathAndFilename($src);
                if (file_exists($pathAndFilename)) {
                    $imageTag->setAttribute('src', 'cid:' . FileUtility::getFilenameFromPathAndFilename($pathAndFilename));
                }
            }
        }
        return $this->dom->saveHTML();
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
}

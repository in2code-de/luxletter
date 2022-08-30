<?php

declare(strict_types = 1);

namespace In2code\Luxletter\Domain\Service\BodytextManipulation\ImageEmbedding;

use DOMDocument;
use DOMElement;
use In2code\Luxletter\Exception\MisconfigurationException;
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
     * Get bodytext of a mail and rewrite src to (e.g.) "cig:name_1"
     *
     * Example bodytext:
     * <img src="/var/www/imagehash1.jpg">
     * <img src="/var/www/imagehash2.jpg">
     * <img src="/var/www/imagehash1.jpg">
     *
     * Example return value:
     *  [
     *      'name_ab0662afe84da6407fa0920a4d339494' => '/var/www/imagehash1.jpg',
     *      'name_39261b47697d687db54f32f0a7e26a43' => '/var/www/imagehash2.jpg',
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
                $embedName = $this->getEmbedNameForPathAndFilename($pathAndFilename);
                if (!isset($images[$embedName]) && file_exists($pathAndFilename)) {
                    $images[$embedName] = $pathAndFilename;
                }
            }
        }
        return $images;
    }

    /**
     * Rewrite src to "cid:name_0123456789abcdef0123456789abcdef"
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
                    $imageTag->setAttribute('src', 'cid:' . $this->getEmbedNameForPathAndFilename($pathAndFilename));
                }
            }
        }
        return $this->dom->saveHTML();
    }

    /**
     * @param string $pathAndFilename
     * @return string "name_0123456789abcdef0123456789abcdef"
     */
    protected function getEmbedNameForPathAndFilename(string $pathAndFilename): string
    {
        return 'name_' . \md5($pathAndFilename);
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

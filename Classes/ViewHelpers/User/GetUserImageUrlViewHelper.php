<?php

declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\User;

use In2code\Luxletter\Domain\Model\User;
use TYPO3\CMS\Core\Resource\Exception\InvalidFileException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetUserImageUrlViewHelper
 * @noinspection PhpUnused
 */
class GetUserImageUrlViewHelper extends AbstractViewHelper
{
    /**
     * @var string
     */
    protected string $defaultFile = 'EXT:luxletter/Resources/Public/Images/DummyUser.svg';

    /**
     * Size in px
     *
     * @var int
     */
    protected int $size = 32;

    /**
     * @return void
     */
    public function initializeArguments()
    {
        $this->registerArgument('user', User::class, 'user', true);
        $this->registerArgument('size', 'int', 'size for width and height', false, $this->size);
    }

    /**
     * @return string
     * @throws InvalidFileException
     */
    public function render(): string
    {
        $url = '';
        $url = $this->getImageUrlFromFrontenduser($url);
        $url = $this->getImageUrlFromGravatar($url);
        $url = $this->getDefaultUrl($url);
        return $url;
    }

    /**
     * @param string $url
     * @return string
     */
    protected function getImageUrlFromFrontenduser(string $url): string
    {
        if ($this->isFrontendUserWithImage()) {
            foreach ($this->getUser()->getImage() as $imageObject) {
                $file = $imageObject->getOriginalResource()->getOriginalFile();
                $imageService = GeneralUtility::makeInstance(ImageService::class);
                /** @noinspection PhpInternalEntityUsedInspection */
                $image = $imageService->getImage('', $file, false);
                $processConfiguration = [
                    'width' => (string)$this->arguments['size'] . 'c',
                    'height' => (string)$this->arguments['size'] . 'c',
                ];
                $processedImage = $imageService->applyProcessingInstructions($image, $processConfiguration);
                $url = $imageService->getImageUri($processedImage, true);
            }
        }
        return $url;
    }

    /**
     * @param string $url
     * @return string
     * @throws InvalidFileException
     */
    protected function getImageUrlFromGravatar(string $url): string
    {
        if (empty($url) && $this->getUser()->getEmail() !== '') {
            $gravatarUrl = 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($this->getUser()->getEmail())))
                . '?d=' . urlencode($this->getDefaultUrl($url)) . '&s=' . $this->arguments['size'];
            $header = GeneralUtility::getUrl($gravatarUrl, 2);
            if (!empty($header)) {
                $url = $gravatarUrl;
            }
        }
        return $url;
    }

    /**
     * @param string $url
     * @return string
     * @throws InvalidFileException
     */
    protected function getDefaultUrl(string $url): string
    {
        if (empty($url)) {
            $url = PathUtility::getPublicResourceWebPath($this->defaultFile);
        }
        return $url;
    }

    /**
     * @return User
     */
    protected function getUser(): User
    {
        return $this->arguments['user'];
    }

    /**
     * @return bool
     */
    protected function isFrontendUserWithImage(): bool
    {
        return $this->getUser() !== null
            && $this->getUser()->getImage() !== null
            && $this->getUser()->getImage()->count() > 0;
    }
}

<?php
declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\User;

use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Utility\FrontendUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetUserImageUrlViewHelper
 */
class GetUserImageUrlViewHelper extends AbstractViewHelper
{
    /**
     * @var string
     */
    protected $defaultFile = 'typo3conf/ext/luxletter/Resources/Public/Images/DummyUser.svg';

    /**
     * Size in px
     *
     * @var int
     */
    protected $size = 32;

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
     * @throws Exception
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
     * @throws Exception
     */
    protected function getImageUrlFromFrontenduser(string $url): string
    {
        if ($this->isFrontendUserWithImage()) {
            foreach ($this->getUser()->getImage() as $imageObject) {
                $file = $imageObject->getOriginalResource()->getOriginalFile();
                $imageService = ObjectUtility::getObjectManager()->get(ImageService::class);
                $image = $imageService->getImage('', $file, false);
                $processConfiguration = [
                    'width' => (string)$this->arguments['size'] . 'c',
                    'height' => (string)$this->arguments['size'] . 'c'
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
     */
    protected function getDefaultUrl(string $url): string
    {
        if (empty($url)) {
            $url = FrontendUtility::getCurrentUri() . $this->defaultFile;
        }
        return $url;
    }

    /**
     * @return User
     */
    protected function getUser(): User
    {
        /** @var User $user */
        $user = $this->arguments['user'];
        return $user;
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

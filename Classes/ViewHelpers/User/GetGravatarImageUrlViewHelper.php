<?php
declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\User;

use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetGravatarImageUrlViewHelper
 */
class GetGravatarImageUrlViewHelper extends AbstractViewHelper
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
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $url = $this->getDefaultUrl();
        /** @var User $user */
        $user = $this->arguments['user'];
        $gravatarUrl = 'https://www.gravatar.com/avatar/' . md5(strtolower($user->getEmail()))
            . '?d=' . urlencode($url) . '&s=' . $this->size;
        $header = GeneralUtility::getUrl($gravatarUrl, 2);
        if (!empty($header)) {
            $url = $gravatarUrl;
        }
        return $url;
    }

    /**
     * @return string
     */
    protected function getDefaultUrl(): string
    {
        return StringUtility::getCurrentUri() . $this->defaultFile;
    }
}

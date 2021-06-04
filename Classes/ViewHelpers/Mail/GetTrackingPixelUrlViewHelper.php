<?php
declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Mail;

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetTrackingPixelUrlViewHelper
 * @noinspection PhpUnused
 */
class GetTrackingPixelUrlViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('newsletter', Newsletter::class, 'Newsletter', false, null);
        $this->registerArgument('user', User::class, 'User', false, null);
    }

    /**
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws MisconfigurationException
     */
    public function render(): string
    {
        $url = ConfigurationUtility::getCurrentDomain();
        $url .= '/?type=1561894816';
        $url .= '&tx_luxletter_fe[user]=' . $this->getUserIdentifier();
        $url .= '&tx_luxletter_fe[newsletter]=' . $this->getNewsletterIdentifier();
        return $url;
    }

    /**
     * @return int
     */
    protected function getUserIdentifier(): int
    {
        /** @var User $user */
        $user = $this->arguments['user'];
        if ($user !== null && $user->getUid() > 0) {
            return $user->getUid();
        }
        return 0;
    }

    /**
     * @return int
     */
    protected function getNewsletterIdentifier(): int
    {
        /** @var Newsletter $newsletter */
        $newsletter = $this->arguments['newsletter'];
        if ($newsletter !== null && $newsletter->getUid() > 0) {
            return $newsletter->getUid();
        }
        return 0;
    }
}

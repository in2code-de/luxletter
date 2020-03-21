<?php
declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Mail;

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Service\FrontendUrlService;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Exception\UserValuesAreMissingException;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Routing\InvalidRouteArgumentsException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetUnsubscribeUrlViewHelper
 * @noinspection PhpUnused
 */
class GetUnsubscribeUrlViewHelper extends AbstractViewHelper
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
     * @throws InvalidRouteArgumentsException
     * @throws SiteNotFoundException
     * @throws UserValuesAreMissingException
     * @throws MisconfigurationException
     * @throws Exception
     */
    public function render(): string
    {
        $frontendUrlService = ObjectUtility::getObjectManager()->get(FrontendUrlService::class);
        $url = $frontendUrlService->getTypolinkUrlFromParameter(
            ConfigurationUtility::getPidUnsubscribe(),
            [
                'tx_luxletter_fe' => [
                    'user' => $this->getUserIdentifier(),
                    'newsletter' => $this->getNewsletterIdentifier(),
                    'hash' => $this->getHash()
                ]
            ]
        );
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

    /**
     * @return string
     * @throws UserValuesAreMissingException
     */
    protected function getHash(): string
    {
        /** @var User $user */
        $user = $this->arguments['user'];
        if ($user !== null && $user->getUid() > 0) {
            return $user->getUnsubscribeHash();
        }
        return '';
    }
}

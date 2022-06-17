<?php
declare(strict_types = 1);
namespace In2code\Luxletter\ViewHelpers\Mail;

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Service\SiteService;
use In2code\Luxletter\Exception\MisconfigurationException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        $this->registerArgument('newsletter', Newsletter::class, 'Newsletter', false);
        $this->registerArgument('user', User::class, 'User', false);
        $this->registerArgument('site', Site::class, 'Site object', true);
    }

    /**
     * @return string
     * @throws MisconfigurationException
     */
    public function render(): string
    {
        $url = $this->getDomainPrefix();
        $url .= '?type=1561894816';
        $url .= '&tx_luxletter_fe[user]=' . $this->getUserIdentifier();
        $url .= '&tx_luxletter_fe[newsletter]=' . $this->getNewsletterIdentifier();
        return $url;
    }

    /**
     * @return string
     * @throws MisconfigurationException
     */
    protected function getDomainPrefix(): string
    {
        /** @var Site $site */
        $site = $this->arguments['site'];
        $siteService = GeneralUtility::makeInstance(SiteService::class);
        return $siteService->getDomainFromSite($site);
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

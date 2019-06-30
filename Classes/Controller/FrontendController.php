<?php
declare(strict_types=1);
namespace In2code\Luxletter\Controller;

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Service\LogService;
use In2code\Luxletter\Domain\Service\ParseNewsletterUrlService;
use In2code\Luxletter\Utility\BackendUserUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class FrontendController
 */
class FrontendController extends ActionController
{
    /**
     * @return void
     */
    public function initializePreviewAction(): void
    {
        if (BackendUserUtility::isBackendUserAuthenticated() === false) {
            throw new \LogicException('You are not authenticated to see this view', 1560778826);
        }
    }

    /**
     * @param string $origin
     * @return string
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws InvalidConfigurationTypeException
     */
    public function previewAction(string $origin): string
    {
        $urlService = ObjectUtility::getObjectManager()->get(ParseNewsletterUrlService::class, $origin);
        return $urlService->getParsedContent();
    }

    /**
     * Render a transparent gif and track the access as email-opening
     *
     * @param Newsletter|null $newsletter
     * @param User|null $user
     * @return string
     * @throws IllegalObjectTypeException
     */
    public function trackingPixelAction(Newsletter $newsletter = null, User $user = null): string
    {
        if ($newsletter !== null && $user !== null) {
            $logService = ObjectUtility::getObjectManager()->get(LogService::class);
            $logService->logNewsletterOpening($newsletter, $user);
        }
        return base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
    }
}

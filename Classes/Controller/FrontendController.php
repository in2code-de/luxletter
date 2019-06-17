<?php
declare(strict_types=1);
namespace In2code\Luxletter\Controller;

use In2code\Luxletter\Domain\Service\ParseNewsletterUrlService;
use In2code\Luxletter\Utility\BackendUserUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

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
     */
    public function previewAction(string $origin): string
    {
        $urlService = ObjectUtility::getObjectManager()->get(ParseNewsletterUrlService::class, $origin);
        $content = $urlService->getParsedContent();
        return $content;
    }
}

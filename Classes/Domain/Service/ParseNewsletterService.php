<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Signal\SignalTrait;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class ParseNewsletterService to fill out variables for newsletter subject or bodytext
 */
class ParseNewsletterService
{
    use SignalTrait;

    /**
     * @param string $bodytext
     * @param User $user
     * @return string
     */
    public function parseBodytext(string $bodytext, User $user): string
    {
        $standaloneView = ObjectUtility::getObjectManager()->get(StandaloneView::class);
        $standaloneView->setTemplateSource($bodytext);
        $standaloneView->assignMultiple(['user' => $user]);
        return $standaloneView->render();
    }
}

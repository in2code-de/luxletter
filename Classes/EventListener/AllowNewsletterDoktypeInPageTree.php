<?php

declare(strict_types=1);
namespace In2code\Luxletter\EventListener;

use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\BeforeLoadedUserTsConfigEvent;

/**
 * Allow backend users to drag and drop the newsletter page doktype in the page tree (multi language mode only).
 * TYPO3 14 shows all doktypes the user is allowed to create automatically (option is deprecated since TYPO3 14.2).
 *
 * Todo: Can be removed when TYPO3 13 support is dropped
 */
#[AsEventListener('luxletter/allowNewsletterDoktypeInPageTree')]
class AllowNewsletterDoktypeInPageTree
{
    public function __invoke(BeforeLoadedUserTsConfigEvent $event): void
    {
        if ((new Typo3Version())->getMajorVersion() < 14 && ConfigurationUtility::isMultiLanguageModeActivated()) {
            $event->addTsConfig(
                'options.pageTree.doktypesToShowInNewPageDragArea := addToList('
                . ConfigurationUtility::getMultilanguageNewsletterPageDoktype() . ')'
            );
        }
    }
}

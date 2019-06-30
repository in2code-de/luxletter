<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Signal\SignalTrait;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class ParseNewsletterService to fill out variables for newsletter subject or bodytext
 */
class ParseNewsletterService
{
    use SignalTrait;

    /**
     * @param string $bodytext
     * @param array $properties
     * @return string
     * @throws InvalidConfigurationTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function parseMailText(string $bodytext, array $properties): string
    {
        $configuration = ConfigurationUtility::getExtensionSettings();
        $standaloneView = ObjectUtility::getObjectManager()->get(StandaloneView::class);
        $standaloneView->setTemplateRootPaths($configuration['view']['templateRootPaths']);
        $standaloneView->setLayoutRootPaths($configuration['view']['layoutRootPaths']);
        $standaloneView->setPartialRootPaths($configuration['view']['partialRootPaths']);
        $standaloneView->setTemplateSource($bodytext);
        $standaloneView->assignMultiple($properties);
        $string = $standaloneView->render();
        $this->signalDispatch(__CLASS__, __FUNCTION__, [$string, $properties, $this]);
        return $string;
    }
}

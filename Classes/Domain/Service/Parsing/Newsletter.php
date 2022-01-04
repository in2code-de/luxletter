<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Service\Parsing;

use In2code\Luxletter\Signal\SignalTrait;
use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class Newsletter
 * to fill out variables for newsletter subject or bodytext when a newsletter is final send
 */
class Newsletter
{
    use SignalTrait;

    /**
     * @param string $subject
     * @param array $properties
     * @return string
     * @throws Exception
     * @throws InvalidConfigurationTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function parseSubject(string $subject, array $properties): string
    {
        $this->signalDispatch(__CLASS__, __FUNCTION__, [&$subject, $properties, $this]);
        $properties['type'] = 'subject';
        return $this->parseMailText($subject, $properties);
    }

    /**
     * @param string $bodytext
     * @param array $properties
     * @return string @throws Exception
     * @throws InvalidConfigurationTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws Exception
     */
    public function parseBodytext(string $bodytext, array $properties): string
    {
        $this->signalDispatch(__CLASS__, __FUNCTION__, [&$bodytext, $properties, $this]);
        $properties['type'] = 'bodytext';
        return $this->parseMailText($bodytext, $properties);
    }

    /**
     * @param string $text
     * @param array $properties
     * @return string
     * @throws InvalidConfigurationTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws Exception
     */
    protected function parseMailText(string $text, array $properties): string
    {
        $configuration = ConfigurationUtility::getExtensionSettings();
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $standaloneView->setTemplateRootPaths($configuration['view']['templateRootPaths']);
        $standaloneView->setLayoutRootPaths($configuration['view']['layoutRootPaths']);
        $standaloneView->setPartialRootPaths($configuration['view']['partialRootPaths']);
        $standaloneView->setTemplateSource($text);
        $standaloneView->assignMultiple($properties);
        $string = $standaloneView->render();
        $this->signalDispatch(__CLASS__, __FUNCTION__, [&$string, $properties, $this]);
        return $string;
    }
}
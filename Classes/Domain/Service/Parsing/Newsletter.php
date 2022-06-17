<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Service\Parsing;

use In2code\Luxletter\Events\NewsletterParseBodytextEvent;
use In2code\Luxletter\Events\NewsletterParseMailtextEvent;
use In2code\Luxletter\Events\NewsletterParseSubjectEvent;
use In2code\Luxletter\Utility\ConfigurationUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class Newsletter
 * to fill out variables for newsletter subject or bodytext when a newsletter is final send
 */
class Newsletter
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string $subject
     * @param array $properties
     * @return string
     * @throws InvalidConfigurationTypeException
     */
    public function parseSubject(string $subject, array $properties): string
    {
        $properties['type'] = 'subject';
        /** @var NewsletterParseSubjectEvent $event */
        $event = $this->eventDispatcher->dispatch(
            GeneralUtility::makeInstance(NewsletterParseSubjectEvent::class, $subject, $properties)
        );
        return $this->parseMailText($event->getSubject(), $event->getProperties());
    }

    /**
     * @param string $bodytext
     * @param array $properties
     * @return string
     * @throws InvalidConfigurationTypeException
     */
    public function parseBodytext(string $bodytext, array $properties): string
    {
        $properties['type'] = 'bodytext';
        /** @var NewsletterParseBodytextEvent $event */
        $event = $this->eventDispatcher->dispatch(
            GeneralUtility::makeInstance(NewsletterParseBodytextEvent::class, $bodytext, $properties)
        );
        return $this->parseMailText($event->getBodytext(), $event->getProperties());
    }

    /**
     * @param string $text
     * @param array $properties
     * @return string
     * @throws InvalidConfigurationTypeException
     */
    protected function parseMailText(string $text, array $properties): string
    {
        if (!empty($text)) {
            $configuration = ConfigurationUtility::getExtensionSettings();
            $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
            $standaloneView->setTemplateRootPaths($configuration['view']['templateRootPaths']);
            $standaloneView->setLayoutRootPaths($configuration['view']['layoutRootPaths']);
            $standaloneView->setPartialRootPaths($configuration['view']['partialRootPaths']);
            $standaloneView->setTemplateSource($text);
            $standaloneView->assignMultiple($properties);
            $text = $standaloneView->render();
            /** @var NewsletterParseMailtextEvent $event */
            $event = $this->eventDispatcher->dispatch(
                GeneralUtility::makeInstance(NewsletterParseMailtextEvent::class, $text, $properties)
            );
            $text = $event->getText();
        }
        return $text;
    }
}

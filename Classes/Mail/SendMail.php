<?php

declare(strict_types=1);
namespace In2code\Luxletter\Mail;

use In2code\Luxletter\Domain\Model\Configuration;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Service\BodytextManipulation\ImageEmbedding\Execution;
use In2code\Luxletter\Domain\Service\UnsubscribeUrlService;
use In2code\Luxletter\Events\SendMailSendNewsletterBeforeEvent;
use In2code\Luxletter\Events\SendMailSendNewsletterBeforeMailMessageEvent;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\ConfigurationUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SendMail
 * is used for testing and for final newsletter mails
 */
class SendMail
{
    protected string $subject = '';
    protected string $bodytext = '';

    protected ?Configuration $configuration = null;
    protected ?Newsletter $newsletter = null;
    protected ?User $user = null;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        string $subject,
        string $bodytext,
        Configuration $configuration,
        ?Newsletter $newsletter = null,
        ?User $user = null
    ) {
        $this->subject = $subject;
        $this->bodytext = $bodytext;
        $this->configuration = $configuration;
        $this->newsletter = $newsletter;
        $this->user = $user;
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
    }

    /**
     * @param array $receiver ['a@mail.org' => 'Receivername']
     * @return bool the number of recipients who were accepted for delivery
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws MisconfigurationException
     */
    public function sendNewsletter(array $receiver): bool
    {
        /** @var SendMailSendNewsletterBeforeEvent $event */
        $event = $this->eventDispatcher->dispatch(GeneralUtility::makeInstance(
            SendMailSendNewsletterBeforeEvent::class,
            $receiver,
            $this
        ));
        if ($event->isSend()) {
            $mailMessage = GeneralUtility::makeInstance(MailMessage::class);
            $mailMessage
                ->setTo($receiver)
                ->setFrom([$this->configuration->getFromEmail() => $this->configuration->getFromName()])
                ->setReplyTo([$this->configuration->getReplyEmail() => $this->configuration->getReplyName()])
                ->setSubject($this->subject)
                ->html($this->getBodytext($mailMessage));
            $this->setUnsubscribeUrlInHeader($mailMessage);

            /** @var SendMailSendNewsletterBeforeMailMessageEvent $event */
            $event = $this->eventDispatcher->dispatch(GeneralUtility::makeInstance(
                SendMailSendNewsletterBeforeMailMessageEvent::class,
                $receiver,
                $mailMessage,
                $this
            ));
            if ($event->isSend() === true) {
                return $mailMessage->send();
            }
        }
        return false;
    }

    /**
     * Get the bodytext (with or without embedded images)
     *
     * @param MailMessage $mailMessage
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws MisconfigurationException
     */
    protected function getBodytext(MailMessage $mailMessage): string
    {
        $imageEmbedding = GeneralUtility::makeInstance(Execution::class);
        if ($imageEmbedding->isActive()) {
            $imageEmbedding->setBodytext($this->bodytext);
            $images = $imageEmbedding->getImages();
            foreach ($images as $hash => $pathAndFilename) {
                $mailMessage->embedFromPath($pathAndFilename, $hash);
            }
            return $imageEmbedding->getRewrittenContent();
        }
        return $this->bodytext;
    }

    /**
     * @param MailMessage $mailMessage
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws MisconfigurationException
     */
    protected function setUnsubscribeUrlInHeader(MailMessage $mailMessage): void
    {
        if (ConfigurationUtility::isUnsubscribeUrlToMailHeaderActivated()) {
            $unsubscribeUrlService = GeneralUtility::makeInstance(
                UnsubscribeUrlService::class,
                $this->newsletter,
                $this->user,
                $this->configuration->getSiteConfiguration(),
                $this->newsletter?->getLanguage() ?? 0
            );
            $headers = $mailMessage->getHeaders();
            $headers->addHeader('List-Unsubscribe', '<' . $unsubscribeUrlService->get() . '>');
            $headers->addHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        }
    }
}

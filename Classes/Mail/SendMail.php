<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Mail;

use In2code\Luxletter\Domain\Model\Configuration;
use In2code\Luxletter\Domain\Service\BodytextManipulation\ImageEmbedding\Execution;
use In2code\Luxletter\Events\SendMailSendNewsletterBeforeEvent;
use In2code\Luxletter\Events\SendMailSendNewsletterBeforeMailMessageEvent;
use In2code\Luxletter\Exception\MisconfigurationException;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class SendMail
 * is used for testmails and for final newsletter mails
 */
class SendMail
{
    /**
     * @var string
     */
    protected $subject = '';

    /**
     * @var string
     */
    protected $bodytext = '';

    /**
     * @var Configuration|null
     */
    protected $configuration = null;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * SendMail constructor.
     * @param string $subject
     * @param string $bodytext
     * @param Configuration $configuration
     */
    public function __construct(string $subject, string $bodytext, Configuration $configuration)
    {
        $this->subject = $subject;
        $this->bodytext = $bodytext;
        $this->configuration = $configuration;
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
}

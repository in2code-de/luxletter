<?php
declare(strict_types=1);
namespace In2code\Luxletter\Mail;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use TYPO3\CMS\Core\Mail\MailMessage as MailMessageCore;

/**
 * Class MailMessage
 */
class MailMessage extends MailMessageCore
{
    /**
     * Inject own Mailer class to overwrite mail settings
     */
    private function initializeMailer()
    {
        $this->mailer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Mailer::class);
    }

    /**
     * Sends the message.
     *
     * This is a short-hand method. It is however more useful to create
     * a Mailer instance which can be used via Mailer->send($message);
     *
     * @return bool whether the message was accepted or not
     * @throws TransportExceptionInterface
     */
    public function send(): bool
    {
        $this->initializeMailer();
        $this->sent = false;
        $this->mailer->send($this);
        $sentMessage = $this->mailer->getSentMessage();
        if ($sentMessage) {
            $this->sent = true;
        }
        return $this->sent;
    }
}

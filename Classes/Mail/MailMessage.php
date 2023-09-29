<?php

declare(strict_types=1);
namespace In2code\Luxletter\Mail;

use TYPO3\CMS\Core\Mail\MailMessage as MailMessageCore;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class MailMessage extends MailMessageCore
{
    /**
     * Inject own Mailer class to overwrite mail settings
     */
    private function initializeMailer()
    {
        $this->mailer = GeneralUtility::makeInstance(Mailer::class);
    }

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

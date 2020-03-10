<?php
declare(strict_types=1);
namespace In2code\Luxletter\Mail;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use TYPO3\CMS\Core\Mail\MailMessage as MailMessageCore;
use TYPO3\CMS\Core\Utility\MailUtility;

/**
 * Class MailMessageLegacy
 * Todo: Can be removed when TYPO3 9 suppored is dropped
 */
class MailMessageLegacy extends MailMessageCore
{
    /**
     * Inject own Mailer class to overwrite mail settings
     */
    private function initializeMailer()
    {
        $this->mailer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(Mailer::class);
    }

    /**
     * Sends the message and call our initializeMailer() function
     *
     * @return int the number of recipients who were accepted for delivery
     * @throws TransportExceptionInterface
     */
    public function send()
    {
        // Ensure to always have a From: header set
        if (empty($this->getFrom())) {
            $this->setFrom(MailUtility::getSystemFrom());
        }
        if (empty($this->getReplyTo())) {
            $replyTo = MailUtility::getSystemReplyTo();
            if (!empty($replyTo)) {
                $this->setReplyTo($replyTo);
            }
        }
        $this->initializeMailer();
        $this->sent = true;
        $this->getHeaders()->addTextHeader('X-Mailer', $this->mailerHeader);
        return $this->mailer->send($this, $this->failedRecipients);
    }
}

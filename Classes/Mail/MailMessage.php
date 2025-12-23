<?php

declare(strict_types=1);
namespace In2code\Luxletter\Mail;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
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

    /**
     * @return bool
     * @throws TransportExceptionInterface
     */
    public function send(): bool
    {
        $this->initializeMailer();
        $this->sent = false;
        try {
            $this->mailer->send($this);
        } finally {
            // In case of SMTP always close the connection to the SMTP-server, especially in case of failure.
            // This prevents "421 Too many concurrent SMTP connections".
            if (method_exists($this->mailer->getTransport(), 'stop')) {
                // See Symfony\Component\Mailer\Transport\Smtp\SmtpTransport::stop()
                // https://github.com/symfony/mailer/blob/f466aa7c9ad74159986f91fda49a364b3bd9a2b6/Transport/Smtp/SmtpTransport.php#L295-L311
                // Compare to Symfony\Component\Mailer\Transport\Smtp\SmtpTransport::ping()
                // https://github.com/symfony/mailer/blob/f466aa7c9ad74159986f91fda49a364b3bd9a2b6/Transport/Smtp/SmtpTransport.php#L313-L324
                $this->mailer->getTransport()->stop();
            }
        }
        $sentMessage = $this->mailer->getSentMessage();
        if ($sentMessage) {
            $this->sent = true;
        }
        return $this->sent;
    }
}

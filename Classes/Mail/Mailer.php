<?php

declare(strict_types=1);
namespace In2code\Luxletter\Mail;

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use TYPO3\CMS\Core\Exception as ExceptionCore;
use TYPO3\CMS\Core\Mail\Mailer as MailerCore;

class Mailer extends MailerCore
{
    /**
     * @param TransportInterface|null $transport
     * @param EventDispatcherInterface|null $eventDispatcher
     * @throws ExceptionCore
     */
    public function __construct(?TransportInterface $transport = null, ?EventDispatcherInterface $eventDispatcher = null)
    {
        parent::__construct($transport, $eventDispatcher);
        $this->transport = $this->getTransportFactory()->get($this->getMailSettings());
    }

    /**
     * @param MailMessage $mailMessage
     * @return bool
     * @throws TransportExceptionInterface
     */
    public function sendMailMessage(MailMessage $mailMessage): bool
    {
        try {
            $this->send($mailMessage);
        } finally {
            // In case of SMTP always close the connection to the SMTP-server, especially in case of failure.
            // This prevents "421 Too many concurrent SMTP connections".
            if (method_exists($this->getTransport(), 'stop')) {
                // See Symfony\Component\Mailer\Transport\Smtp\SmtpTransport::stop()
                // https://github.com/symfony/mailer/blob/f466aa7c9ad74159986f91fda49a364b3bd9a2b6/Transport/Smtp/SmtpTransport.php#L295-L311
                // Compare to Symfony\Component\Mailer\Transport\Smtp\SmtpTransport::ping()
                // https://github.com/symfony/mailer/blob/f466aa7c9ad74159986f91fda49a364b3bd9a2b6/Transport/Smtp/SmtpTransport.php#L313-L324
                $this->getTransport()->stop();
            }
        }
        return $this->getSentMessage() !== null;
    }

    /**
     * Use own mail settings and overrule TYPO3 mail configuration if available
     *
     *  Example configuration for luxletter only:
     *     $GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport'] = 'smtp';
     *     $GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport_smtp_server'] = 'sslout.de:465';
     *     $GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport_smtp_encrypt'] = 'ssl';
     *     $GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport_smtp_username'] = 'username';
     *     $GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport_smtp_password'] = 'password';
     *     $GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport_smtp_port'] = '465';
     *
     * @return array
     */
    protected function getMailSettings(): array
    {
        return array_merge(
            $GLOBALS['TYPO3_CONF_VARS']['MAIL'] ?? [],
            $GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER'] ?? []
        );
    }
}

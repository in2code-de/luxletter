<?php

declare(strict_types=1);
namespace In2code\Luxletter\Mail;

use Psr\EventDispatcher\EventDispatcherInterface;
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

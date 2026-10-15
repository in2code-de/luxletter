<?php

namespace In2code\Luxletter\Tests\Functional\Fixtures\Mail;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;

/**
 * Mail transport for functional tests that keeps sent mails in memory instead of delivering them.
 * Register in $configurationToUseInTestInstance as ['MAIL_LUXLETTER' => ['transport' => MemoryTransport::class]]
 */
class MemoryTransport extends AbstractTransport
{
    /**
     * @var Email[]
     */
    public static array $messages = [];

    /**
     * TYPO3 TransportFactory passes the mail settings to custom transports
     *
     * @param array $mailSettings
     */
    public function __construct(array $mailSettings = [])
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        self::$messages[] = $message->getOriginalMessage();
    }

    public function __toString(): string
    {
        return 'memory://';
    }
}

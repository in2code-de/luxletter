<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Mail\SendMail;

final class SendMailSendNewsletterBeforeEvent
{
    /**
     * @var bool
     */
    protected $send = true;

    /**
     * @var array
     */
    protected $receiver;

    /**
     * @var SendMail
     */
    protected $sendMail;

    /**
     * @param array $receiver
     * @param SendMail $sendMail
     */
    public function __construct(array $receiver, SendMail $sendMail)
    {
        $this->receiver = $receiver;
        $this->sendMail = $sendMail;
    }

    /**
     * @return bool
     */
    public function isSend(): bool
    {
        return $this->send;
    }

    /**
     * @param bool $send
     * @return SendMailSendNewsletterBeforeEvent
     */
    public function setSend(bool $send): SendMailSendNewsletterBeforeEvent
    {
        $this->send = $send;
        return $this;
    }

    /**
     * @return array
     */
    public function getReceiver(): array
    {
        return $this->receiver;
    }

    /**
     * @param array $receiver
     * @return SendMailSendNewsletterBeforeEvent
     */
    public function setReceiver(array $receiver): SendMailSendNewsletterBeforeEvent
    {
        $this->receiver = $receiver;
        return $this;
    }

    /**
     * @return SendMail
     */
    public function getSendMail(): SendMail
    {
        return $this->sendMail;
    }
}

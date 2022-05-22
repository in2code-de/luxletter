<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Mail\MailMessage;
use In2code\Luxletter\Mail\SendMail;

final class SendMailSendNewsletterBeforeMailMessageEvent
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
     * @var MailMessage
     */
    protected $mailMessage;

    /**
     * @var SendMail
     */
    protected $sendMail;

    /**
     * @param array $receiver
     * @param MailMessage $mailMessage
     * @param SendMail $sendMail
     */
    public function __construct(array $receiver, MailMessage $mailMessage, SendMail $sendMail)
    {
        $this->receiver = $receiver;
        $this->mailMessage = $mailMessage;
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
     * @return SendMailSendNewsletterBeforeMailMessageEvent
     */
    public function setSend(bool $send): SendMailSendNewsletterBeforeMailMessageEvent
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
     * @return SendMailSendNewsletterBeforeMailMessageEvent
     */
    public function setReceiver(array $receiver): SendMailSendNewsletterBeforeMailMessageEvent
    {
        $this->receiver = $receiver;
        return $this;
    }

    /**
     * @return MailMessage
     */
    public function getMailMessage(): MailMessage
    {
        return $this->mailMessage;
    }

    /**
     * @return SendMail
     */
    public function getSendMail(): SendMail
    {
        return $this->sendMail;
    }
}

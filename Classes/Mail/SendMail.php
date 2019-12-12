<?php
declare(strict_types=1);
namespace In2code\Luxletter\Mail;

use In2code\Luxletter\Signal\SignalTrait;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class SendMail
 */
class SendMail
{
    use SignalTrait;

    /**
     * @var string
     */
    protected $subject = '';

    /**
     * @var string
     */
    protected $bodytext = '';

    /**
     * @var string
     */
    protected $fromEmail = '';

    /**
     * @var string
     */
    protected $fromName = '';

    /**
     * @var string
     */
    protected $replyEmail = '';

    /**
     * @var string
     */
    protected $replyName = '';

    /**
     * MailService constructor.
     * @param string $subject
     * @param string $bodytext
     */
    public function __construct(string $subject, string $bodytext, string $fromEmail, string $fromName, string $replyEmail, string $replyName)
    {
        $this->subject = $subject;
        $this->bodytext = $bodytext;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->replyEmail = $replyEmail;
        $this->replyName = $replyName;
    }

    /**
     * @param string $email
     * @return int the number of recipients who were accepted for delivery
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function sendNewsletter(string $email): int
    {
        $send = true;
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'beforeSend', [&$send, $email, $this]);
        $mailMessage = ObjectUtility::getObjectManager()->get(MailMessage::class);
        $mailMessage
            ->setTo([$email => 'Newsletter receiver'])
            ->setFrom([$this->fromEmail => $this->fromName])
            ->setReplyTo([$this->replyEmail => $this->replyName])
            ->setSubject($this->subject)
            ->setBody($this->bodytext, 'text/html');
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'mailMessage', [$mailMessage, &$send, $email, $this]);
        if ($send === true) {
            return $mailMessage->send();
        }
        return 0;
    }
}

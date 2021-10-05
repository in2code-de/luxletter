<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Mail;

use In2code\Luxletter\Domain\Model\Configuration;
use In2code\Luxletter\Signal\SignalTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
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
     * @var Configuration|null
     */
    protected $configuration = null;

    /**
     * SendMail constructor.
     * @param string $subject
     * @param string $bodytext
     * @param Configuration $configuration
     */
    public function __construct(string $subject, string $bodytext, Configuration $configuration)
    {
        $this->subject = $subject;
        $this->bodytext = $bodytext;
        $this->configuration = $configuration;
    }

    /**
     * @param array $receiver ['a@mail.org' => 'Receivername']
     * @return bool the number of recipients who were accepted for delivery
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws Exception
     */
    public function sendNewsletter(array $receiver): bool
    {
        $send = true;
        $this->signalDispatch(__CLASS__, 'sendbeforeSend', [&$send, $receiver, $this]);
        $mailMessage = GeneralUtility::makeInstance(MailMessage::class);
        $mailMessage
            ->setTo($receiver)
            ->setFrom([$this->configuration->getFromEmail() => $this->configuration->getFromName()])
            ->setReplyTo([$this->configuration->getReplyEmail() => $this->configuration->getReplyName()])
            ->setSubject($this->subject)
            ->html($this->bodytext);
        $this->signalDispatch(__CLASS__, 'sendmailMessage', [$mailMessage, &$send, $receiver, $this]);
        if ($send === true) {
            return $mailMessage->send();
        }
        return false;
    }
}

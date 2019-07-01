<?php
declare(strict_types=1);
namespace In2code\Luxletter\Mail;

use In2code\Luxletter\Signal\SignalTrait;
use In2code\Luxletter\Utility\ConfigurationUtility;
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
     * MailService constructor.
     * @param string $subject
     * @param string $bodytext
     */
    public function __construct(string $subject, string $bodytext)
    {
        $this->subject = $subject;
        $this->bodytext = $bodytext;
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
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'beforeSend', [$this]);
        $mailMessage = ObjectUtility::getObjectManager()->get(MailMessage::class);
        $mailMessage
            ->setTo([$email => 'Newsletter receiver'])
            ->setFrom([ConfigurationUtility::getFromEmail() => ConfigurationUtility::getFromName()])
            ->setReplyTo([ConfigurationUtility::getReplyEmail() => ConfigurationUtility::getReplyName()])
            ->setSubject($this->subject)
            ->setBody($this->bodytext, 'text/html');
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'mailMessage', [$mailMessage]);
        return $mailMessage->send();
    }
}

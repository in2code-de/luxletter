<?php
declare(strict_types=1);
namespace In2code\Luxletter\Mail;

use In2code\Luxletter\Signal\SignalTrait;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
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
     * @return bool the number of recipients who were accepted for delivery
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws TransportExceptionInterface
     * @throws Exception
     */
    public function sendNewsletter(string $email): bool
    {
        if (ConfigurationUtility::isVersionToCompareSameOrLowerThenCurrentTypo3Version('10.0.0')) {
            // TYPO3 10
            return $this->send($email);
        } else {
            // TYPO3 9
            return $this->sendLegacy($email);
        }
    }

    /**
     * Send with new MailMessage from TYPO3 10
     *
     * @param string $email
     * @return bool
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws TransportExceptionInterface
     */
    protected function send(string $email): bool
    {
        $send = true;
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'beforeSend', [&$send, $email, $this]);
        $mailMessage = ObjectUtility::getObjectManager()->get(MailMessage::class);
        $mailMessage
            ->setTo([$email => 'Newsletter receiver'])
            ->setFrom([ConfigurationUtility::getFromEmail() => ConfigurationUtility::getFromName()])
            ->setReplyTo([ConfigurationUtility::getReplyEmail() => ConfigurationUtility::getReplyName()])
            ->setSubject($this->subject)
            ->html($this->bodytext, 'text/html');
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'mailMessage', [$mailMessage, &$send, $email, $this]);
        if ($send === true) {
            // Todo: Can be renamed to send() when TYPO3 9 support is dropped
            return $mailMessage->sendMail();
        }
        return false;
    }

    /**
     * Send with old MailMessage from TYPO3 9
     *
     * @param string $email
     * @return bool
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws TransportExceptionInterface
     * Todo: Can be removed when TYPO3 9 support is dropped
     */
    protected function sendLegacy(string $email): bool
    {
        $send = true;
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'beforeSendLegacy', [&$send, $email, $this]);
        $mailMessage = ObjectUtility::getObjectManager()->get(MailMessageLegacy::class);
        $mailMessage
            ->setTo([$email => 'Newsletter receiver'])
            ->setFrom([ConfigurationUtility::getFromEmail() => ConfigurationUtility::getFromName()])
            ->setReplyTo([ConfigurationUtility::getReplyEmail() => ConfigurationUtility::getReplyName()])
            ->setSubject($this->subject)
            ->setBody($this->bodytext, 'text/html');
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'mailMessageLegacy', [$mailMessage, &$send, $email, $this]);
        if ($send === true) {
            return $mailMessage->send() > 0;
        }
        return false;
    }
}

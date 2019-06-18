<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Signal\SignalTrait;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class MailService
 * Todo make sender configurable
 */
class MailService
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
    protected $fromEmail = 'alex@in2code.de';

    /**
     * @var string
     */
    protected $fromName = 'Alex Kellner';

    /**
     * @var string
     */
    protected $replyEmail = 'alex@in2code.de';

    /**
     * @var string
     */
    protected $replyName = 'Alex Kellner';

    /**
     * MailService constructor.
     * @param string $subject
     * @param string $origin
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function __construct(string $subject, string $origin)
    {
        $this->subject = $subject;
        $parseService = ObjectUtility::getObjectManager()->get(ParseNewsletterUrlService::class, $origin);
        $this->bodytext = $parseService->getParsedContent();
    }

    /**
     * @param string $email
     * @return int the number of recipients who were accepted for delivery
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function sendNewsletter(string $email): int
    {
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'beforeSend', [$this]);
        $mailMessage = ObjectUtility::getObjectManager()->get(MailMessage::class);
        $mailMessage
            ->setTo([$email => 'Newsletter receiver'])
            ->setFrom([$this->fromEmail => $this->fromName])
            ->setReplyTo([$this->replyEmail => $this->replyName])
            ->setSubject($this->subject)
            ->setBody($this->bodytext, 'text/html');
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'mailMessage', [$mailMessage]);
        return $mailMessage->send();
    }
}

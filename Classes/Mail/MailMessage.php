<?php

declare(strict_types=1);
namespace In2code\Luxletter\Mail;

use TYPO3\CMS\Core\Mail\MailMessage as MailMessageCore;

/**
 * Class MailMessage
 * is a pure data object. Mails are sent with Mailer::sendMailMessage() since MailMessage->send() was removed in TYPO3 14.
 */
class MailMessage extends MailMessageCore
{
}

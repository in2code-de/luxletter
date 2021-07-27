<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Mail;

use TYPO3\CMS\Core\Mail\MailMessage as MailMessageCore;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class MailMessage
 */
class MailMessage extends MailMessageCore
{
    /**
     * Inject own Mailer class to overwrite mail settings
     */
    private function initializeMailer()
    {
        $this->mailer = GeneralUtility::makeInstance(Mailer::class);
    }
}

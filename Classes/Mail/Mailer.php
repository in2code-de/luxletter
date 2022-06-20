<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Mail;

use TYPO3\CMS\Core\Mail\Mailer as MailerCore;

/**
 * Class MailMessage overwrite the core Mail
 */
class Mailer extends MailerCore
{
    /**
     * Use own mail settings and overrule TYPO3 mailsettings or if not set fallback to default values
     *
     *  Example configuration for luxletter only:
     *     $GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport'] = 'smtp';
     *     $GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport_smtp_server'] = 'sslout.de:465';
     *     $GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport_smtp_encrypt'] = 'ssl';
     *     $GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport_smtp_username'] = 'username';
     *     $GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport_smtp_password'] = 'password';
     *     $GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']['transport_smtp_port'] = '465';
     *
     * @param array|null $mailSettings
     * @return void
     */
    public function injectMailSettings(array $mailSettings = null)
    {
        if (is_array($mailSettings)) {
            $this->mailSettings = $mailSettings;
        } else {
            $this->mailSettings = $this->getMailSettings();
        }
    }

    /**
     * @return array
     */
    protected function getMailSettings(): array
    {
        return array_merge(
            (array)$GLOBALS['TYPO3_CONF_VARS']['MAIL'],
            (array)$GLOBALS['TYPO3_CONF_VARS']['MAIL_LUXLETTER']
        );
    }
}

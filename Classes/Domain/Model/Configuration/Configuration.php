<?php /** @noinspection PhpInternalEntityUsedInspection */
/** @noinspection PhpUnused */
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model\Configuration;


use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;

class Configuration
{

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var string
     */
    protected $title;

    /**
     * Configuration constructor.
     * @param string $identifier
     * @param array $configuration
     * @param string $title
     * @param string $description
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function __construct(string $identifier, array $configuration, string $title, string $description = '')
    {
        $baseConfiguration = [
            'fromEmail' => ConfigurationUtility::getFromEmail(),
            'fromName' => ConfigurationUtility::getFromName(),
            'replyEmail' => ConfigurationUtility::getReplyEmail(),
            'replyName' => ConfigurationUtility::getReplyName(),
            'pidUnsubscribe' => ConfigurationUtility::getPidUnsubscribe(),
            'rewriteLinksInNewsletter' => ConfigurationUtility::isRewriteLinksInNewsletterActivated(),
        ];
        $this->identifier = $identifier;
        $this->title = $title;
        $this->description = $description;
        $this->configuration = array_merge($baseConfiguration, $configuration);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getPidUnsubscribe(): int
    {
        return (int)$this->configuration['pidUnsubscribe'];
    }

    public function isRewriteLinksInNewsletterActivated(): bool
    {
        return (bool)$this->configuration['rewriteLinksInNewsletterActivated'];
    }

    public function getFromEmail(): string
    {
        return $this->configuration['fromEmail'];
    }

    public function getFromName(): string
    {
        return $this->configuration['fromName'];
    }

    public function getReplyEmail(): string
    {
        return $this->configuration['replyEmail'];
    }

    public function getReplyName(): string
    {
        return $this->configuration['replyName'];
    }

}
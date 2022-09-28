<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model;

use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Settings
 */
class Configuration extends AbstractEntity
{
    const TABLE_NAME = 'tx_luxletter_domain_model_configuration';

    /**
     * @var string
     */
    protected $title = '';

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
     * @var string
     */
    protected $site = '';

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Configuration
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromEmail(): string
    {
        return $this->fromEmail;
    }

    /**
     * @param string $fromEmail
     * @return Configuration
     */
    public function setFromEmail(string $fromEmail): self
    {
        $this->fromEmail = $fromEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getFromName(): string
    {
        return $this->fromName;
    }

    /**
     * @param string $fromName
     * @return Configuration
     */
    public function setFromName(string $fromName): self
    {
        $this->fromName = $fromName;
        return $this;
    }

    /**
     * @return string
     */
    public function getReplyEmail(): string
    {
        return $this->replyEmail;
    }

    /**
     * @param string $replyEmail
     * @return Configuration
     */
    public function setReplyEmail(string $replyEmail): self
    {
        $this->replyEmail = $replyEmail;
        return $this;
    }

    /**
     * @return string
     */
    public function getReplyName(): string
    {
        return $this->replyName;
    }

    /**
     * @param string $replyName
     * @return Configuration
     */
    public function setReplyName(string $replyName): self
    {
        $this->replyName = $replyName;
        return $this;
    }

    /**
     * @return string
     */
    public function getSite(): string
    {
        return $this->site;
    }

    /**
     * @return Site
     * @throws SiteNotFoundException
     */
    public function getSiteConfiguration(): Site
    {
        $identifier = $this->getSite();
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        return $siteFinder->getSiteByIdentifier($identifier);
    }

    /**
     * @param string $site
     * @return Configuration
     */
    public function setSite(string $site): self
    {
        $this->site = $site;
        return $this;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model;

use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Configuration extends AbstractEntity
{
    const TABLE_NAME = 'tx_luxletter_domain_model_configuration';

    protected string $title = '';
    protected string $fromEmail = '';
    protected string $fromName = '';
    protected string $replyEmail = '';
    protected string $replyName = '';
    protected string $site = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getFromEmail(): string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(string $fromEmail): self
    {
        $this->fromEmail = $fromEmail;
        return $this;
    }

    public function getFromName(): string
    {
        return $this->fromName;
    }

    public function setFromName(string $fromName): self
    {
        $this->fromName = $fromName;
        return $this;
    }

    public function getReplyEmail(): string
    {
        return $this->replyEmail;
    }

    public function setReplyEmail(string $replyEmail): self
    {
        $this->replyEmail = $replyEmail;
        return $this;
    }

    public function getReplyName(): string
    {
        return $this->replyName;
    }

    public function setReplyName(string $replyName): self
    {
        $this->replyName = $replyName;
        return $this;
    }

    public function getSite(): string
    {
        return $this->site;
    }

    public function getSiteConfiguration(): Site
    {
        $identifier = $this->getSite();
        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        return $siteFinder->getSiteByIdentifier($identifier);
    }

    public function setSite(string $site): self
    {
        $this->site = $site;
        return $this;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model;

use In2code\Luxletter\Domain\Service\SiteService;
use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Link extends AbstractEntity
{
    const TABLE_NAME = 'tx_luxletter_domain_model_link';

    protected string $hash = '';
    protected string $target = '';

    protected ?Newsletter $newsletter = null;
    protected ?User $user = null;

    public function getNewsletter(): ?Newsletter
    {
        return $this->newsletter;
    }

    public function setNewsletter(Newsletter $newsletter): self
    {
        $this->newsletter = $newsletter;
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;
        return $this;
    }

    public function getUriFromHash(): string
    {
        $site = $this->getNewsletter()->getConfiguration()->getSiteConfiguration();
        $siteService = GeneralUtility::makeInstance(SiteService::class);
        return $siteService->getFrontendUrlFromParameter(['luxletterlink' => $this->getHash()], $site);
    }

    public function getTarget(): string
    {
        return $this->target;
    }

    public function setTarget(string $target): self
    {
        $this->target = $target;
        $this->setHashFromTarget($target);
        return $this;
    }

    private function setHashFromTarget(string $target): void
    {
        if ($this->getHash() === '' && $this->getNewsletter() !== null && $this->getUser() !== null) {
            $this->setHash($this->getHashFromTarget($target));
        }
    }

    private function getHashFromTarget(string $target): string
    {
        $parts = [
            $target,
            $this->getUser()->getUid(),
            $this->getNewsletter()->getUid(),
        ];
        return StringUtility::getHashFromArguments($parts);
    }
}

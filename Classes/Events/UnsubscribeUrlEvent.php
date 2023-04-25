<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use TYPO3\CMS\Core\Site\Entity\Site;

final class UnsubscribeUrlEvent
{
    protected string $url;

    protected ?Newsletter $newsletter;
    protected ?User $user;
    protected Site $site;

    public function __construct(string $url, ?Newsletter $newsletter, ?User $user, Site $site)
    {
        $this->url = $url;
        $this->newsletter = $newsletter;
        $this->user = $user;
        $this->site = $site;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getNewsletter(): ?Newsletter
    {
        return $this->newsletter;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getSite(): Site
    {
        return $this->site;
    }
}

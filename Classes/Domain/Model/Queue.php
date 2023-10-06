<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Queue extends AbstractEntity
{
    const TABLE_NAME = 'tx_luxletter_domain_model_queue';

    protected string $email = '';

    protected bool $sent = false;

    protected int $failures = 0;

    protected ?DateTime $datetime = null;
    protected ?Newsletter $newsletter = null;
    protected string $bodytext = '';
    protected ?User $user = null;

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getNewsletter(): ?Newsletter
    {
        return $this->newsletter;
    }

    public function setNewsletter(Newsletter $newsletter): self
    {
        $this->newsletter = $newsletter;
        return $this;
    }

    public function getBodytext(): string
    {
        return $this->bodytext;
    }

    public function setBodytext(string $bodytext): self
    {
        $this->bodytext = $bodytext;
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

    public function getDatetime(): ?DateTime
    {
        return $this->datetime;
    }

    public function setDatetime(DateTime $datetime): self
    {
        $this->datetime = $datetime;
        return $this;
    }

    public function isSent(): bool
    {
        return $this->sent;
    }

    public function setSent(): self
    {
        $this->sent = true;
        return $this;
    }

    public function getFailures(): int
    {
        return $this->failures;
    }

    public function setFailures(int $failures): self
    {
        $this->failures = $failures;
        return $this;
    }
}

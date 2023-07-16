<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Log extends AbstractEntity
{
    const TABLE_NAME = 'tx_luxletter_domain_model_log';
    const STATUS_DEFAULT = 0;
    const STATUS_DISPATCH = 100;
    const STATUS_DISPATCH_FAILURE = 110;
    const STATUS_NEWSLETTEROPENING = 200;
    const STATUS_LINKOPENING = 300;
    const STATUS_UNSUBSCRIBE = 400;

    protected string $properties = '';
    protected int $status = self::STATUS_DEFAULT;

    protected ?DateTime $crdate = null;
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

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getProperties(): array
    {
        $propertiesRaw = $this->properties;
        if ($propertiesRaw !== '') {
            return json_decode($propertiesRaw, true);
        }
        return [];
    }

    public function setProperties(array $properties): self
    {
        $this->properties = json_encode($properties);
        return $this;
    }

    public function getCrdate(): ?DateTime
    {
        return $this->crdate;
    }

    public function setCrdate(DateTime $crdate): self
    {
        $this->crdate = $crdate;
        return $this;
    }
}

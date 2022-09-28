<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model;

use DateTime;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Log
 */
class Log extends AbstractEntity
{
    const TABLE_NAME = 'tx_luxletter_domain_model_log';
    const STATUS_DEFAULT = 0;
    const STATUS_DISPATCH = 100;
    const STATUS_NEWSLETTEROPENING = 200;
    const STATUS_LINKOPENING = 300;
    const STATUS_UNSUBSCRIBE = 400;

    /**
     * @var Newsletter
     */
    protected $newsletter = null;

    /**
     * @var User
     */
    protected $user = null;

    /**
     * @var int
     */
    protected $status = self::STATUS_DEFAULT;

    /**
     * @var string
     */
    protected $properties = '';

    /**
     * @var DateTime
     */
    protected $crdate = null;

    /**
     * @return Newsletter|null
     */
    public function getNewsletter(): ?Newsletter
    {
        return $this->newsletter;
    }

    /**
     * @param Newsletter $newsletter
     * @return Log
     */
    public function setNewsletter(Newsletter $newsletter): self
    {
        $this->newsletter = $newsletter;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Log
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return Log
     */
    public function setStatus(int $status): self
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        $propertiesRaw = $this->properties;
        if ($propertiesRaw !== '') {
            return json_decode($propertiesRaw, true);
        }
        return [];
    }

    /**
     * @param array $properties
     * @return Log
     */
    public function setProperties(array $properties): self
    {
        $this->properties = json_encode($properties);
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getCrdate(): ?DateTime
    {
        return $this->crdate;
    }

    /**
     * @param DateTime $crdate
     * @return Log
     */
    public function setCrdate(DateTime $crdate): self
    {
        $this->crdate = $crdate;
        return $this;
    }
}

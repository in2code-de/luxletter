<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Queue
 */
class Queue extends AbstractEntity
{
    const TABLE_NAME = 'tx_luxletter_domain_model_queue';

    /**
     * @var string
     */
    protected $email = '';

    /**
     * @var \In2code\Luxletter\Domain\Model\Newsletter
     */
    protected $newsletter = null;

    /**
     * @var \In2code\Luxletter\Domain\Model\User
     */
    protected $user = null;

    /**
     * @var \DateTime
     */
    protected $datetime = null;

    /**
     * @var bool
     */
    protected $sent = false;

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Queue
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return Newsletter
     */
    public function getNewsletter(): Newsletter
    {
        return $this->newsletter;
    }

    /**
     * @param Newsletter $newsletter
     * @return Queue
     */
    public function setNewsletter(Newsletter $newsletter): self
    {
        $this->newsletter = $newsletter;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Queue
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDatetime(): \DateTime
    {
        return $this->datetime;
    }

    /**
     * @param \DateTime $datetime
     * @return Queue
     */
    public function setDatetime(\DateTime $datetime): self
    {
        $this->datetime = $datetime;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSent(): bool
    {
        return $this->sent;
    }

    /**
     * @return Queue
     */
    public function setSent(): self
    {
        $this->sent = true;
        return $this;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Usergroup extends AbstractEntity
{
    public const TABLE_NAME = 'fe_groups';

    protected string $title = '';
    protected string $description = '';
    protected bool $luxletterReceiver = false;

    /**
     * @var ObjectStorage<Usergroup>
     */
    protected ?ObjectStorage $subgroup = null;

    public function __construct(string $title = '')
    {
        $this->setTitle($title);
        $this->subgroup = new ObjectStorage();
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setSubgroup(ObjectStorage $subgroup): self
    {
        $this->subgroup = $subgroup;
        return $this;
    }

    public function addSubgroup(Usergroup $subgroup): self
    {
        $this->subgroup->attach($subgroup);
        return $this;
    }

    public function removeSubgroup(Usergroup $subgroup): self
    {
        $this->subgroup->detach($subgroup);
        return $this;
    }

    public function getSubgroup(): ObjectStorage
    {
        if ($this->subgroup === null) {
            $this->subgroup = new ObjectStorage();
        }
        return $this->subgroup;
    }

    public function isLuxletterReceiver(): bool
    {
        return $this->luxletterReceiver;
    }

    public function setLuxletterReceiver(bool $luxletterReceiver): self
    {
        $this->luxletterReceiver = $luxletterReceiver;
        return $this;
    }
}

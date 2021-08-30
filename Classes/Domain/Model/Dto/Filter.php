<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Model\Dto;

use In2code\Luxletter\Domain\Model\Usergroup;

/**
 * Class Filter
 */
class Filter
{

    /**
     * @var string
     */
    protected $searchterm = '';

    /**
     * @var Usergroup
     */
    protected $usergroup = null;

    /**
     * This is just a dummy property, that helps to recognize if a filter is set and save this to the session
     *
     * @var bool
     */
    protected $reset = false;

    /**
     * @return string
     */
    public function getSearchterm(): string
    {
        return $this->searchterm;
    }

    /**
     * @return array
     */
    public function getSearchterms(): array
    {
        return explode(' ', $this->getSearchterm());
    }

    /**
     * @param string $searchterm
     * @return Filter
     */
    public function setSearchterm(string $searchterm): self
    {
        $this->searchterm = $searchterm;
        return $this;
    }

    /**
     * @return Usergroup
     */
    public function getUsergroup(): ?Usergroup
    {
        return $this->usergroup;
    }

    /**
     * @param Usergroup $usergroup
     * @return Filter
     */
    public function setUsergroup(?Usergroup $usergroup): self
    {
        $this->usergroup = $usergroup;
        return $this;
    }

    /**
     * @return bool
     */
    public function isReset(): bool
    {
        return $this->reset;
    }

    /**
     * @param bool $reset
     * @return Filter
     */
    public function setReset(bool $reset): self
    {
        $this->reset = $reset;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSet(): bool
    {
        return $this->searchterm !== '' || $this->usergroup !== null;
    }
}

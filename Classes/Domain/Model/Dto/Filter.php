<?php

declare(strict_types=1);
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
     * @var \In2code\Lux\Domain\Model\Category|null
     */
    protected $category = null;

    /**
     * This is just a dummy property, that helps to recognize if a filter is set and helps to save this to the session
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
     * @return string[]
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
     * @param Usergroup|null $usergroup
     * @return Filter
     */
    public function setUsergroup(?Usergroup $usergroup): self
    {
        $this->usergroup = $usergroup;
        return $this;
    }

    /**
     * @return \In2code\Lux\Domain\Model\Category|null
     */
    public function getCategory(): ?\In2code\Lux\Domain\Model\Category
    {
        return $this->category;
    }

    /**
     * @param \In2code\Lux\Domain\Model\Category|null $category
     * @return Filter
     */
    public function setCategory(?\In2code\Lux\Domain\Model\Category $category): Filter
    {
        $this->category = $category;
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
        return $this->searchterm !== '' || $this->usergroup !== null || $this->category !== null;
    }
}

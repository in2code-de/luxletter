<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model\Dto;

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
     * @var \In2code\Luxletter\Domain\Model\Usergroup
     */
    protected $usergroup = null;

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
     * @return \In2code\Luxletter\Domain\Model\Usergroup
     */
    public function getUsergroup(): ?\In2code\Luxletter\Domain\Model\Usergroup
    {
        return $this->usergroup;
    }

    /**
     * @param \In2code\Luxletter\Domain\Model\Usergroup $usergroup
     * @return Filter
     */
    public function setUsergroup(?\In2code\Luxletter\Domain\Model\Usergroup $usergroup): self
    {
        $this->usergroup = $usergroup;
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

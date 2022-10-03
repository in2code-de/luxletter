<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model\Dto;

use DateTime;
use In2code\Luxletter\Domain\Model\Usergroup;
use In2code\Luxletter\Utility\LocalizationUtility;

/**
 * Class Filter
 */
class Filter
{
    const TIME_DEFAULT = 0;
    const TIME_1_WEEK = 10;
    const TIME_1_MONTH = 20;
    const TIME_3_MONTHS = 30;
    const TIME_6_MONTHS = 40;
    const TIME_1_YEAR = 50;

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
     * @var int
     */
    protected $time = self::TIME_DEFAULT;

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
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @return DateTime
     */
    public function getTimeDateStart(): DateTime
    {
        $date = new DateTime();
        switch ($this->getTime()) {
            case self::TIME_1_WEEK:
                $date->modify('-1 week');
                break;
            case self::TIME_1_MONTH:
                $date->modify('-1 month');
                break;
            case self::TIME_3_MONTHS:
                $date->modify('-3 months');
                break;
            case self::TIME_6_MONTHS:
                $date->modify('-6 months');
                break;
            case self::TIME_1_YEAR:
                $date->modify('-1 year');
                break;
        }
        return $date;
    }

    /**
     * @param int $time
     * @return Filter
     */
    public function setTime(int $time): Filter
    {
        $this->time = $time;
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
        return $this->searchterm !== '' || $this->usergroup !== null || $this->category !== null || $this->time > 0;
    }

    public function getTimeOptions(): array
    {
        $llPrefix = 'module.newsletter.list.filter.time.';
        return [
            self::TIME_DEFAULT => LocalizationUtility::translate($llPrefix . self::TIME_DEFAULT),
            self::TIME_1_WEEK => LocalizationUtility::translate($llPrefix . self::TIME_1_WEEK),
            self::TIME_1_MONTH => LocalizationUtility::translate($llPrefix . self::TIME_1_MONTH),
            self::TIME_3_MONTHS => LocalizationUtility::translate($llPrefix . self::TIME_3_MONTHS),
            self::TIME_6_MONTHS => LocalizationUtility::translate($llPrefix . self::TIME_6_MONTHS),
            self::TIME_1_YEAR => LocalizationUtility::translate($llPrefix . self::TIME_1_YEAR),
        ];
    }
}

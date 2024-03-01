<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model\Dto;

use DateTime;
use In2code\Luxletter\Domain\Model\Category;
use In2code\Luxletter\Domain\Model\Configuration;
use In2code\Luxletter\Domain\Model\Usergroup;
use In2code\Luxletter\Utility\LocalizationUtility;

class Filter
{
    public const TIME_DEFAULT = 0;
    public const TIME_1_WEEK = 10;
    public const TIME_1_MONTH = 20;
    public const TIME_3_MONTHS = 30;
    public const TIME_6_MONTHS = 40;
    public const TIME_1_YEAR = 50;

    protected string $searchterm = '';

    protected ?Usergroup $usergroup = null;
    protected ?Category $category = null;
    protected ?Configuration $configuration = null;

    protected int $time = self::TIME_DEFAULT;

    /**
     * This is just a dummy property, that helps to recognize if a filter is set and helps to save this to the session
     *
     * @var bool
     */
    protected bool $reset = false;

    public function getSearchterm(): string
    {
        return $this->searchterm;
    }

    public function isSearchtermSet(): bool
    {
        return $this->getSearchterm() !== '';
    }

    public function getSearchterms(): array
    {
        return explode(' ', $this->getSearchterm());
    }

    public function setSearchterm(string $searchterm): self
    {
        $this->searchterm = $searchterm;
        return $this;
    }

    public function getUsergroup(): ?Usergroup
    {
        return $this->usergroup;
    }

    public function isUsergroupSet(): bool
    {
        return $this->getUsergroup() !== null;
    }

    public function setUsergroup(?Usergroup $usergroup): self
    {
        $this->usergroup = $usergroup;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function isCategorySet(): bool
    {
        return $this->getCategory() !== null;
    }

    public function setCategory(?Category $category): Filter
    {
        $this->category = $category;
        return $this;
    }

    public function getConfiguration(): ?Configuration
    {
        return $this->configuration;
    }

    public function isConfigurationSet(): bool
    {
        return $this->getConfiguration() !== null;
    }

    public function setConfiguration(?Configuration $configuration): self
    {
        $this->configuration = $configuration;
        return $this;
    }

    public function getTime(): int
    {
        return $this->time;
    }

    public function isTimeSet(): bool
    {
        return $this->getTime() !== self::TIME_DEFAULT;
    }

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

    public function setTime(int $time): Filter
    {
        $this->time = $time;
        return $this;
    }

    public function isReset(): bool
    {
        return $this->reset;
    }

    public function setReset(bool $reset): self
    {
        $this->reset = $reset;
        return $this;
    }

    public function isSet(): bool
    {
        return $this->isSearchtermSet()
            || $this->isConfigurationSet()
            || $this->isUsergroupSet()
            || $this->isCategorySet()
            || $this->isTimeSet();
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

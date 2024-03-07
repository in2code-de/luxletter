<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model\Dto;

use DateTime;
use In2code\Luxletter\Domain\Model\Category;
use In2code\Luxletter\Domain\Model\Configuration;
use In2code\Luxletter\Domain\Model\Usergroup;
use In2code\Luxletter\Domain\Service\SiteService;
use In2code\Luxletter\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Filter
{
    public const TIME_DEFAULT = 0;
    public const TIME_1_WEEK = 10;
    public const TIME_1_MONTH = 20;
    public const TIME_3_MONTHS = 30;
    public const TIME_6_MONTHS = 40;
    public const TIME_1_YEAR = 50;

    protected string $searchterm = '';
    protected string $site = '';

    protected ?Usergroup $usergroup = null;
    protected ?Category $category = null;
    protected ?Configuration $configuration = null;

    protected int $time = self::TIME_DEFAULT;
    protected int $limit = 0;

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

    public function getSite(): string
    {
        return $this->site;
    }

    public function isSiteSet(): bool
    {
        return $this->getSite() !== '';
    }

    public function setSite(string $site): self
    {
        // Don't allow to pass not allowed site in filter
        if (array_key_exists($site, $this->getAllowedSites()) === false) {
            $site = '';
        }
        $this->site = $site;
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
            case self::TIME_DEFAULT:
                $date = new DateTime('2000-01-01T00:00:00+02:00');
        }
        return $date;
    }

    public function setTime(int $time): Filter
    {
        $this->time = $time;
        return $this;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function isLimitSet(): bool
    {
        return $this->getLimit() > 0;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
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
            || $this->isSiteSet()
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

    /**
     * Get all sites on which the current editor has reading access
     *
     * @return array
     */
    public function getAllowedSites(): array
    {
        $siteService = GeneralUtility::makeInstance(SiteService::class);
        return $siteService->getAllowedSites();
    }

    /**
     * Always return given site or all available sites, so this can be always used in sql queries even for admins
     *
     * @return array
     */
    public function getSitesForFilter(): array
    {
        if ($this->isSiteSet()) {
            return [$this->getSite()];
        }
        return array_merge(array_keys($this->getAllowedSites()), ['']);
    }
}

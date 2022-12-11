<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Category extends AbstractEntity
{
    const TABLE_NAME = 'sys_category';

    protected string $title = '';

    protected bool $luxletterNewsletterCategory = false;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): Category
    {
        $this->title = $title;
        return $this;
    }

    public function isLuxletterNewsletterCategory(): bool
    {
        return $this->luxletterNewsletterCategory;
    }

    public function setLuxletterNewsletterCategory(bool $luxletterNewsletterCategory): Category
    {
        $this->luxletterNewsletterCategory = $luxletterNewsletterCategory;
        return $this;
    }
}

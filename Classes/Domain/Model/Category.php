<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Category
 */
class Category extends AbstractEntity
{
    const TABLE_NAME = 'sys_category';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var bool
     */
    protected $luxletterNewsletterCategory = false;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Category
     */
    public function setTitle(string $title): Category
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLuxletterNewsletterCategory(): bool
    {
        return $this->luxletterNewsletterCategory;
    }

    /**
     * @param bool $luxletterNewsletterCategory
     * @return Category
     */
    public function setLuxletterNewsletterCategory(bool $luxletterNewsletterCategory): Category
    {
        $this->luxletterNewsletterCategory = $luxletterNewsletterCategory;
        return $this;
    }
}

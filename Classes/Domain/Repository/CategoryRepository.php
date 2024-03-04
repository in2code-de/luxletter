<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use In2code\Luxletter\Domain\Model\Category;
use In2code\Luxletter\Domain\Service\PermissionTrait;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class CategoryRepository extends AbstractRepository
{
    use PermissionTrait;

    public function findAllLuxletterCategories(): array
    {
        $query = $this->createQuery();
        $query->matching($query->equals('luxletter_newsletter_category', true));
        $query->setOrderings(['title' => QueryInterface::ORDER_ASCENDING]);
        $records = $query->execute()->toArray();
        return $this->filterRecords($records, Category::TABLE_NAME);
    }
}

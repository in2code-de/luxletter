<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class CategoryRepository
 */
class CategoryRepository extends AbstractRepository
{
    /**
     * @return QueryResultInterface
     */
    public function findAllLuxletterCategories(): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching($query->equals('luxletter_newsletter_category', true));
        $query->setOrderings(['title' => QueryInterface::ORDER_ASCENDING]);
        return $query->execute();
    }
}

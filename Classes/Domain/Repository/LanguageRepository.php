<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Repository;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use In2code\Luxletter\Utility\DatabaseUtility;

/**
 * Class LanguageRepository
 */
class LanguageRepository
{
    const TABLE_NAME = 'sys_language';

    /**
     * @param int $languageIdentifier
     * @return string
     * @throws ExceptionDbalDriver
     */
    public function getTitleFromIdentifier(int $languageIdentifier): string
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME);
        return $queryBuilder
            ->select('title')
            ->from(self::TABLE_NAME)
            ->where('uid=' . (int)$languageIdentifier)
            ->execute()
            ->fetchOne();
    }

    /**
     * @param int $languageIdentifier
     * @return string
     * @throws ExceptionDbalDriver
     */
    public function getIsocodeFromIdentifier(int $languageIdentifier): string
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME);
        return $queryBuilder
            ->select('language_isocode')
            ->from(self::TABLE_NAME)
            ->where('uid=' . (int)$languageIdentifier)
            ->execute()
            ->fetchOne();
    }
}

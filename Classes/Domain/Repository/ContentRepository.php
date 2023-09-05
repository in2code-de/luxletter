<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use In2code\Luxletter\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Service\FlexFormService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ContentRepository extends AbstractRepository
{
    public const TABLE_NAME = 'tt_content';

    public function findConfiguredUsergroupIdentifiersByContentIdentifier(int $identifier): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME);
        $flexForm = $queryBuilder
            ->select('pi_flexform')
            ->from(self::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($identifier, Connection::PARAM_INT))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();
        if ($flexForm !== false) {
            $flexFormService = GeneralUtility::makeInstance(FlexFormService::class);
            $ffConfiguration = $flexFormService->convertFlexFormContentToArray($flexForm);
            return GeneralUtility::intExplode(',', $ffConfiguration['settings']['usergroups'] ?? '', true);
        }
        return [];
    }
}

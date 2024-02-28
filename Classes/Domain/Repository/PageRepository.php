<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\DatabaseUtility;
use PDO;
use Throwable;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\MathUtility;

class PageRepository
{
    const TABLE_NAME = 'pages';

    /**
     * Like
     *  [
     *      123 => 'Title page 1',
     *      124 => 'Title page 2',
     *  ]
     *
     * @return array
     */
    public function findAllNewsletterPages(): array
    {
        $pages = [];
        try {
            $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME);
            $results = $queryBuilder
                ->select('*')
                ->from(self::TABLE_NAME)
                ->where(
                    'doktype=' . ConfigurationUtility::getMultilanguageNewsletterPageDoktype()
                    . ' and sys_language_uid=0'
                )
                ->orderBy('title', 'desc')
                ->executeQuery()
                ->fetchAllAssociative();
            $pages = $this->checkForUserAccess($results);
        } catch (Throwable $exception) {
            return $pages;
        }
        return $pages;
    }

    public function getSubjectFromPageIdentifier(int $pageIdentifier, int $language): string
    {
        try {
            $fieldname = 'uid';
            if ($language > 0) {
                $fieldname = 'l10n_parent';
            }
            $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME);
            $subject = $queryBuilder
                ->select('luxletter_subject')
                ->from(self::TABLE_NAME)
                ->where(
                    $queryBuilder->expr()->eq(
                        $fieldname,
                        $queryBuilder->createNamedParameter($pageIdentifier, PDO::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        'sys_language_uid',
                        $queryBuilder->createNamedParameter($language, PDO::PARAM_INT)
                    )
                )
                ->executeQuery()
                ->fetchOne();
            /** @var string $subject */
            return $subject;
        } catch (Throwable $exception) {
            return '';
        }
    }

    /**
     * Check the pid from origin if there are more localized versions available (only in multilanguage mode)
     *
     * @param string $origin
     * @return array
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws MisconfigurationException
     */
    public function getLanguagesFromOrigin(string $origin): array
    {
        if (ConfigurationUtility::isMultiLanguageModeActivated()) {
            if (MathUtility::canBeInterpretedAsInteger($origin) === false) {
                throw new MisconfigurationException('Origin must be an integer', 1645646455);
            }
            return $this->getLanguagesFromPageIdentifier((int)$origin);
        }
        return [0];
    }

    /**
     * @param array $pages
     * @return array
     */
    private function checkForUserAccess(array $pages): array
    {
        $backendUser = $GLOBALS['BE_USER'];
        if ($backendUser === null) {
            return $pages;
        }
        $checkedPages = [];
        foreach ($pages as $page) {
            $pageAccess = $this->getPageAccess($page);
            if ($pageAccess === 0 && !$backendUser->isAdmin()) {
                continue;
            }
            if ($backendUser->isAdmin() || $backendUser->isMemberOfGroup($pageAccess)) {
                $checkedPages[$page['uid']] = $page['title'];
            }
        }

        return $checkedPages;
    }

    /**
     * @param array $page
     * @return int
     */
    private function getPageAccess(array $page): int
    {
        $permsGroup = $page['perms_groupid'] ?? 1;
        if ($permsGroup !== 1) {
            return (int)$permsGroup;
        }
        /** @var int|string $pid */
        $pid = $page['pid'] ?? 0;
        if ($pid === 0) {
            return 0;
        }
        $page = $this->getPageByUid((int)$pid);

        return $this->getPageAccess($page);
    }

    /**
     * @param int $pid
     * @return array
     */
    private function getPageByUid(int $pid): array
    {
        try {
            $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME);
            $result = $queryBuilder
                ->select('*')
                ->from(self::TABLE_NAME)
                ->where('uid=' . $pid)
                ->executeQuery()
                ->fetchAllAssociative()
            ;
        } catch (Throwable $ex) {
            return [];
        }
        if (array_key_exists(0, $result)) {
            return $result[0];
        }

        return $result;
    }

    protected function getLanguagesFromPageIdentifier(int $pageIdentifier): array
    {
        try {
            $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME);
            $languages = $queryBuilder
                ->select('sys_language_uid')
                ->from(self::TABLE_NAME)
                ->where('l10n_parent=' . $pageIdentifier)
                ->executeQuery()
                ->fetchFirstColumn();
            if ($this->isDefaultLanguageEnabled($pageIdentifier)) {
                $languages = array_merge([0], $languages);
            }
            return $languages;
        } catch (Throwable $exception) {
            return [];
        }
    }

    protected function isDefaultLanguageEnabled(int $pageIdentifier): bool
    {
        try {
            $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME);
            $languageConfiguration = $queryBuilder
                ->select('l18n_cfg')
                ->from(self::TABLE_NAME)
                ->where('uid=' . (int)$pageIdentifier)
                ->executeQuery()
                ->fetchOne();
            return $languageConfiguration !== 1 && $languageConfiguration !== 3;
        } catch (Throwable $exception) {
            return true;
        }
    }
}

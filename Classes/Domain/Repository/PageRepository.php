<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use In2code\Luxletter\Domain\Service\PermissionTrait;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\BackendUserUtility;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\DatabaseUtility;
use Throwable;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Utility\MathUtility;

class PageRepository
{
    use PermissionTrait;

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
            $rows = $queryBuilder
                ->select('*')
                ->from(self::TABLE_NAME)
                ->where(
                    'doktype=' . ConfigurationUtility::getMultilanguageNewsletterPageDoktype()
                    . ' and sys_language_uid=0'
                )
                ->orderBy('title', 'asc')
                ->executeQuery()
                ->fetchAllAssociative();
            if (BackendUserUtility::isAdministrator() === false) {
                foreach ($rows as $key => $row) {
                    if ($this->isAuthenticatedForPageRow($row) === false) {
                        unset($rows[$key]);
                    }
                }
            }
            foreach ($rows as $row) {
                $pages[$row['uid']] = $row['title'];
            }
        } catch (Throwable $exception) {
            unset($exception);
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
                        $queryBuilder->createNamedParameter($pageIdentifier, Connection::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        'sys_language_uid',
                        $queryBuilder->createNamedParameter($language, Connection::PARAM_INT)
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

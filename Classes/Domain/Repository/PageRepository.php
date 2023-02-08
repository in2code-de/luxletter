<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\DatabaseUtility;
use PDO;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Class PageRepository
 */
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
     * @throws ExceptionDbalDriver
     */
    public function findAllNewsletterPages(): array
    {
        $pages = [];
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME);
        $results = $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->where('doktype=' . \In2code\Luxletter\Utility\ConfigurationUtility::getMultilanguageNewsletterPageDoktype() . ' and sys_language_uid=0')
            ->orderBy('title', 'desc')
            ->execute()
            ->fetchAllAssociative();
        foreach ($results as $result) {
            $pages[$result['uid']] = $result['title'];
        }
        return $pages;
    }

    /**
     * @param int $pageIdentifier
     * @param int $language
     * @return string
     * @throws ExceptionDbalDriver
     */
    public function getSubjectFromPageIdentifier(int $pageIdentifier, int $language): string
    {
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
            ->execute()
            ->fetchOne();
        /** @var string $subject */
        return $subject;
    }

    /**
     * Check the pid from origin if there are more localized versions available (only in multilanguage mode)
     *
     * @param string $origin
     * @return array
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ExceptionDbalDriver
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
     * @param int $pageIdentifier
     * @return array
     * @throws ExceptionDbalDriver
     */
    protected function getLanguagesFromPageIdentifier(int $pageIdentifier): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME);
        $languages = $queryBuilder
            ->select('sys_language_uid')
            ->from(self::TABLE_NAME)
            ->where('l10n_parent=' . (int)$pageIdentifier)
            ->execute()
            ->fetchFirstColumn();
        if ($this->isDefaultLanguageEnabled($pageIdentifier)) {
            $languages = array_merge([0], $languages);
        }
        return $languages;
    }

    /**
     * @param int $pageIdentifier
     * @return bool
     * @throws ExceptionDbalDriver
     */
    protected function isDefaultLanguageEnabled(int $pageIdentifier): bool
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME);
        $languageConfiguration = $queryBuilder
            ->select('l18n_cfg')
            ->from(self::TABLE_NAME)
            ->where('uid=' . (int)$pageIdentifier)
            ->execute()
            ->fetchOne();
        return $languageConfiguration !== 1 && $languageConfiguration !== 3;
    }
}

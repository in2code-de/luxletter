<?php

declare(strict_types=1);
namespace In2code\Luxletter\Update;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Exception\DatabaseStructureException;
use In2code\Luxletter\Utility\DatabaseUtility;
use Throwable;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * Class LuxletterReceiversUpdateWizard
 * to copy values of
 * - tx_luxletter_domain_model_newsletter.receiver to .receivers
 */
class LuxletterReceiversUpdateWizard implements UpgradeWizardInterface
{
    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return 'luxletterReceiversUpdateWizard';
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Luxletter: Change some database stuff when upgrading EXT:luxletter to version 17';
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return 'Basicly this upgrade wizard copies values from tx_luxletter_domain_model_newsletter.receiver' .
            ' to .receivers';
    }

    /**
     * @return bool
     */
    public function executeUpdate(): bool
    {
        try {
            $connection = DatabaseUtility::getConnectionForTable(Newsletter::TABLE_NAME);
            $connection->executeQuery('update ' . Newsletter::TABLE_NAME . ' set receivers=receiver;');
        } catch (Throwable $exception) {
            return false;
        }
        return true;
    }

    /**
     * @return bool
     * @throws DBALException
     * @throws DatabaseStructureException
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     */
    public function updateNecessary(): bool
    {
        return $this->isOldFieldFilled() && $this->isNewFieldStillEmpty();
    }

    /**
     * @return string[]
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class,
        ];
    }

    /**
     * @return bool
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    protected function isOldFieldFilled(): bool
    {
        return DatabaseUtility::isFieldFilled('receiver', Newsletter::TABLE_NAME);
    }

    /**
     * @return bool
     * @throws DBALException
     * @throws DatabaseStructureException
     * @throws ExceptionDbalDriver
     * @throws ExceptionDbal
     */
    protected function isNewFieldStillEmpty(): bool
    {
        if (DatabaseUtility::isFieldExistingInTable('receivers', Newsletter::TABLE_NAME) === false) {
            throw new DatabaseStructureException(
                'Field ' . Newsletter::TABLE_NAME . '.receivers is missing. Please do a database compare.',
                1664792430
            );
        }
        return DatabaseUtility::isFieldFilled('receivers', Newsletter::TABLE_NAME) === false;
    }
}

<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Model\Log;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Utility\DatabaseUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class NewsletterRepository
 */
class NewsletterRepository extends AbstractRepository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING
    ];

    /**
     * Remove (really remove) all data from all luxletter tables
     *
     * @return void
     */
    public function truncateAll()
    {
        $tables = [
            Newsletter::TABLE_NAME,
            Link::TABLE_NAME,
            Log::TABLE_NAME,
            Queue::TABLE_NAME
        ];
        foreach ($tables as $table) {
            DatabaseUtility::getConnectionForTable($table)->truncate($table);
        }
    }
}

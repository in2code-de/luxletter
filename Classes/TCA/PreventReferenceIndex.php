<?php

declare(strict_types=1);
namespace In2code\Luxletter\TCA;

use TYPO3\CMS\Core\DataHandling\Event\IsTableExcludedFromReferenceIndexEvent;

/**
 * Prevent reference index records for some Luxletter tables, to keep database small.
 */
class PreventReferenceIndex
{
    protected array $excludedTables = [
        'tx_luxletter_domain_model_link',
        'tx_luxletter_domain_model_log',
        'tx_luxletter_domain_model_queue',
    ];

    public function __invoke(IsTableExcludedFromReferenceIndexEvent $event): void
    {
        if (in_array($event->getTable(), $this->excludedTables, true)) {
            $event->markAsExcluded();
        }
    }
}

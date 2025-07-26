<?php

declare(strict_types=1);
namespace In2code\Luxletter\EventListener;

use TYPO3\CMS\Core\DataHandling\Event\IsTableExcludedFromReferenceIndexEvent;

final class ExcludeTablesFromReferenceIndex
{
    public function __invoke(IsTableExcludedFromReferenceIndexEvent $event): void
    {
        if (in_array($event->getTable(), [
            'tx_luxletter_domain_model_link',
            'tx_luxletter_domain_model_log',
            'tx_luxletter_domain_model_queue',
        ], true)) {
            $event->markAsExcluded();
        }
    }
}

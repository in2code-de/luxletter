<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class SettingsRepository
 */
class ConfigurationRepository extends AbstractRepository
{
    /**
     * @var array
     */
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING,
    ];
}

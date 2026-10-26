<?php

declare(strict_types=1);
namespace In2code\Luxletter\Update;

use TYPO3\CMS\Install\Attribute\UpgradeWizard;
use TYPO3\CMS\Install\Updates\AbstractListTypeToCTypeUpdate;

/**
 * Class LuxletterPluginToContentElementUpdateWizard
 * to migrate luxletter plugins from "list_type" to "CType" in tt_content and be_groups.explicit_allowdeny.
 *
 * Todo: Switch to \TYPO3\CMS\Core\Attribute\UpgradeWizard and \TYPO3\CMS\Core\Upgrades\AbstractListTypeToCTypeUpdate
 *       when TYPO3 13 support is dropped
 */
#[UpgradeWizard('luxletterPluginToContentElementUpdateWizard')]
class LuxletterPluginToContentElementUpdateWizard extends AbstractListTypeToCTypeUpdate
{
    public function getTitle(): string
    {
        return 'Luxletter: Migrate plugins to content elements';
    }

    public function getDescription(): string
    {
        return 'Luxletter plugins are registered as own content elements (CType) instead of the "General plugin"'
            . ' (list_type) now. This wizard migrates existing plugins and backend user group permissions.';
    }

    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'luxletter_fe' => 'luxletter_fe',
            'luxletter_unsubscribe2' => 'luxletter_unsubscribe2',
        ];
    }
}

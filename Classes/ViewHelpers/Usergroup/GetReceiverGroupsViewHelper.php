<?php
declare(strict_types = 1);
namespace In2code\Luxletter\ViewHelpers\Usergroup;

use In2code\Luxletter\Domain\Repository\UsergroupRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetClassNameOnActionViewHelper
 * @noinspection PhpUnused
 */
class GetReceiverGroupsViewHelper extends AbstractViewHelper
{
    /**
     * @return array
     */
    public function render(): array
    {
        $usergroupRepository = GeneralUtility::makeInstance(UsergroupRepository::class);
        return $usergroupRepository->getReceiverGroups();
    }
}

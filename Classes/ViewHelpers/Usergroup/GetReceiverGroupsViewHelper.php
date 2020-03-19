<?php
declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Usergroup;

use In2code\Luxletter\Domain\Repository\UsergroupRepository;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetClassNameOnActionViewHelper
 * @noinspection PhpUnused
 */
class GetReceiverGroupsViewHelper extends AbstractViewHelper
{
    /**
     * @return array
     * @throws Exception
     */
    public function render(): array
    {
        $usergroupRepository = ObjectUtility::getObjectManager()->get(UsergroupRepository::class);
        return $usergroupRepository->getReceiverGroups();
    }
}

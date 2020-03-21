<?php
declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Usergroup;

use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetNumberOfReceiversFromGroupViewHelper
 * @noinspection PhpUnused
 */
class GetNumberOfReceiversFromGroupViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('usergroup', 'int', 'fe_groups.uid', true);
    }

    /**
     * @return int
     * @throws Exception
     */
    public function render(): int
    {
        $userRepository = ObjectUtility::getObjectManager()->get(UserRepository::class);
        return (int)$userRepository->getUsersFromGroup((int)$this->arguments['usergroup'])->count();
    }
}

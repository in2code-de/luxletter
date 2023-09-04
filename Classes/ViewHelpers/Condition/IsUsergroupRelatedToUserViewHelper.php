<?php

declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Condition;

use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Model\Usergroup;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;

class IsUsergroupRelatedToUserViewHelper extends AbstractConditionViewHelper
{
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('user', User::class, 'User with usergroups', true);
        $this->registerArgument('usergroup', Usergroup::class, 'Usergroup to check for', true);
    }

    protected static function evaluateCondition($arguments = null): bool
    {
        /** @var User $user */
        $user = $arguments['user'];
        /** @var Usergroup $usergroup */
        $usergroup = $arguments['usergroup'];
        return $user->getUsergroup()->contains($usergroup);
    }
}

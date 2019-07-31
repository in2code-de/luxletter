<?php
declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Statistic;

use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetListOfMostActivestUsersViewHelper
 */
class GetListOfMostActivestUsersViewHelper extends AbstractViewHelper
{

    /**
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('activities', 'array', 'activities array', true);
        $this->registerArgument('limit', 'int', 'limit', false, 5);
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $activities = $this->getOrderedActivitiesArray();
        $userIdentifiers = array_keys($activities);
        return implode(',', $this->convertUserIdentifiersToNames($userIdentifiers));
    }

    /**
     * @param array $userIdentifiers
     * @return array
     */
    protected function convertUserIdentifiersToNames(array $userIdentifiers): array
    {
        $userRepository = ObjectUtility::getObjectManager()->get(UserRepository::class);
        $names = [];
        foreach ($userIdentifiers as $userIdentifier) {
            /** @var User $user */
            $user = $userRepository->findByUid($userIdentifier);
            $names[] = $user->getReadableName(' ');
        }
        return $names;
    }

    /**
     * @return array
     */
    protected function getOrderedActivitiesArray(): array
    {
        $array = [];
        foreach ($this->arguments['activities'] as $userIdentifier => $activityValues) {
            $array[$userIdentifier] = count($activityValues['activities']);
        }
        arsort($array);
        return array_slice($array, 0, $this->arguments['limit'], true);
    }
}

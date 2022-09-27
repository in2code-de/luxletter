<?php

declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Statistic;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetListOfMostActivestValuesViewHelper
 * @noinspection PhpUnused
 */
class GetListOfMostActivestValuesViewHelper extends AbstractViewHelper
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
        return implode(',', $activities);
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

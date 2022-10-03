<?php

declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\String;

use In2code\Luxletter\Domain\Model\Usergroup;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetDomainViewHelper
 * @noinspection PhpUnused
 */
class JoinGroupTitlesViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('groups', ObjectStorage::class, 'Related groups', true);
        $this->registerArgument('glue', 'string', 'glue character', false, ', ');
    }

    /**
     * @return string
     */
    public function render(): string
    {
        $titles = '';
        /** @var Usergroup $group */
        foreach ($this->arguments['groups'] as $group) {
            if ($titles !== '') {
                $titles .= $this->arguments['glue'];
            }
            $titles .= $group->getTitle();
        }
        return $titles;
    }
}

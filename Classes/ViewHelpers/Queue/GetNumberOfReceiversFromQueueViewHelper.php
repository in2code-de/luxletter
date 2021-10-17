<?php
declare(strict_types = 1);
namespace In2code\Luxletter\ViewHelpers\Queue;

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Repository\QueueRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetNumberOfReceiversFromQueueViewHelper
 * @noinspection PhpUnused
 */
class GetNumberOfReceiversFromQueueViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('newsletter', Newsletter::class, 'Newsletter object', true);
    }

    /**
     * @return int
     */
    public function render(): int
    {
        $userRepository = GeneralUtility::makeInstance(QueueRepository::class);
        return (int)$userRepository->findAllByNewsletter($this->arguments['newsletter'])->count();
    }
}

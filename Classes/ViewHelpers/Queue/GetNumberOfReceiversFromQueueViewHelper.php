<?php
declare(strict_types=1);
namespace In2code\Luxletter\ViewHelpers\Queue;

use In2code\Luxletter\Domain\Repository\QueueRepository;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;
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
        $this->registerArgument('newsletter', 'object', 'Newsletter object', true);
    }

    /**
     * @return int
     * @throws Exception
     */
    public function render(): int
    {
        $userRepository = ObjectUtility::getObjectManager()->get(QueueRepository::class);
        return (int)$userRepository->findAllByNewsletter($this->arguments['newsletter'])->count();
    }
}

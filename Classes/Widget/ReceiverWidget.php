<?php
declare(strict_types=1);
namespace In2code\Luxletter\Widget;

use Doctrine\DBAL\DBALException;
use In2code\Luxletter\Domain\Repository\LogRepository;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\Widgets\AbstractNumberWithIconWidget;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class ReceiverWidget
 */
class ReceiverWidget extends AbstractNumberWithIconWidget
{
    protected $title =
        'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.receiver.title';
    protected $description =
        'LLL:EXT:luxletter/Resources/Private/Language/locallang_db.xlf:module.dashboard.widget.receiver.description';
    protected $iconIdentifier = 'extension-luxletter';
    protected $height = 1;
    protected $width = 1;

    protected $subtitle = '';
    protected $number = 0;
    protected $icon = 'luxletter-widget-receiver';

    /**
     * @return void
     * @throws DBALException
     * @throws Exception
     */
    public function initializeView(): void
    {
        $logRepository = ObjectUtility::getObjectManager()->get(LogRepository::class);
        $this->number = $logRepository->getNumberOfReceivers();
        parent::initializeView();
    }
}

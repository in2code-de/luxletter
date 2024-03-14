<?php

declare(strict_types=1);
namespace In2code\Luxletter\Widget\DataProvider;

use In2code\Luxletter\Domain\Model\Dto\Filter;
use In2code\Luxletter\Domain\Repository\LogRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;

class ReceiverDataProvider implements NumberWithIconDataProviderInterface
{
    public function getNumber(): int
    {
        $logRepository = GeneralUtility::makeInstance(LogRepository::class);
        return $logRepository->getNumberOfReceivers(GeneralUtility::makeInstance(Filter::class));
    }
}

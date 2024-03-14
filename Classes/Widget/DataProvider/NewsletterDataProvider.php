<?php

declare(strict_types=1);
namespace In2code\Luxletter\Widget\DataProvider;

use In2code\Luxletter\Domain\Model\Dto\Filter;
use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;

class NewsletterDataProvider implements NumberWithIconDataProviderInterface
{
    public function getNumber(): int
    {
        $newsletterRepository = GeneralUtility::makeInstance(NewsletterRepository::class);
        return $newsletterRepository->findAllAuthorized(GeneralUtility::makeInstance(Filter::class))->count();
    }
}

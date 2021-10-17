<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Widget\DataProvider;

use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;

/**
 * Class NewsletterDataProvider
 * @noinspection PhpUnused
 */
class NewsletterDataProvider implements NumberWithIconDataProviderInterface
{
    /**
     * @return int
     */
    public function getNumber(): int
    {
        $newsletterRepository = GeneralUtility::makeInstance(NewsletterRepository::class);
        return $newsletterRepository->findAll()->getQuery()->setLimit(10000)->execute()->count();
    }
}

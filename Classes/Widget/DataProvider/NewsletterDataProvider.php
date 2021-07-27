<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Widget\DataProvider;

use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Dashboard\Widgets\NumberWithIconDataProviderInterface;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class NewsletterDataProvider
 * @noinspection PhpUnused
 */
class NewsletterDataProvider implements NumberWithIconDataProviderInterface
{
    /**
     * @return int
     * @throws Exception
     */
    public function getNumber(): int
    {
        $newsletterRepository = ObjectUtility::getObjectManager()->get(NewsletterRepository::class);
        return $newsletterRepository->findAll()->getQuery()->setLimit(10000)->execute()->count();
    }
}

<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Repository;

use In2code\Luxletter\Domain\Service\SiteService;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class LanguageRepository
{
    public function getTitleFromIdentifier(int $languageIdentifier, string $origin): string
    {
        $siteLanguage = $this->getSiteLanguage($languageIdentifier, $origin);
        if ($siteLanguage !== null) {
            return $siteLanguage->getTitle();
        }
        return '';
    }

    public function getIsocodeFromIdentifier(int $languageIdentifier, string $origin): string
    {
        $siteLanguage = $this->getSiteLanguage($languageIdentifier, $origin);
        if ($siteLanguage !== null) {
            return $siteLanguage->getTwoLetterIsoCode();
        }
        return '';
    }

    protected function getSiteLanguage(int $languageIdentifier, string $origin): ?SiteLanguage
    {
        if (MathUtility::canBeInterpretedAsInteger($origin)) {
            $siteService = GeneralUtility::makeInstance(SiteService::class);
            $languages = $siteService->getLanguages((int)$origin);
            if (array_key_exists($languageIdentifier, $languages)) {
                /** @var SiteLanguage $siteLanguage */
                $siteLanguage = $languages[$languageIdentifier];
                return $siteLanguage;
            }
        }
        return null;
    }
}

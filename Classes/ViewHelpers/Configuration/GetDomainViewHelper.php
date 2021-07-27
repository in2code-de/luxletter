<?php
declare(strict_types = 1);
namespace In2code\Luxletter\ViewHelpers\Configuration;

use In2code\Luxletter\Domain\Service\SiteService;
use In2code\Luxletter\Exception\MisconfigurationException;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class GetDomainViewHelper
 * @noinspection PhpUnused
 */
class GetDomainViewHelper extends AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('site', Site::class, 'Site object', true);
    }

    /**
     * @return string like "https://domain.org/"
     * @throws MisconfigurationException
     */
    public function render(): string
    {
        /** @var Site $site */
        $site = $this->arguments['site'];
        /** @var SiteService $siteService */
        $siteService = GeneralUtility::makeInstance(SiteService::class);
        return $siteService->getDomainFromSite($site);
    }
}

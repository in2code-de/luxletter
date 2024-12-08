<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Domain\Factory\UserFactory;
use In2code\Luxletter\Domain\Repository\PageRepository;
use In2code\Luxletter\Domain\Service\Parsing\Newsletter as NewsletterParsing;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

class PreviewUrlService
{
    protected string $origin = '';
    protected string $layout = '';
    protected ?PageRepository $pageRepository = null;
    protected ?NewsletterParsing $parseService = null;
    protected ?UserFactory $userFactory = null;
    protected ?SiteService $siteService = null;

    public function __construct()
    {
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        $this->parseService = GeneralUtility::makeInstance(NewsletterParsing::class);
        $this->userFactory = GeneralUtility::makeInstance(UserFactory::class);
        $this->siteService = GeneralUtility::makeInstance(SiteService::class);
    }

    /**
     * @param string $origin
     * @param string $layout
     * @return array|string[]
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws MisconfigurationException
     * @throws InvalidConfigurationTypeException
     */
    public function get(string $origin, string $layout): array
    {
        if (ConfigurationUtility::isMultiLanguageModeActivated()) {
            return $this->getUrlForMultiLanguageModeInstallation($origin, $layout);
        }
        return $this->getUrlInDefaultInstallation($origin, $layout);
    }

    /**
     * @param string $origin
     * @param string $layout
     * @return array
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws MisconfigurationException
     * @throws InvalidConfigurationTypeException
     */
    protected function getUrlForMultiLanguageModeInstallation(string $origin, string $layout): array
    {
        $urls = [];
        $user = $this->userFactory->getDummyUser();
        foreach ($this->pageRepository->getLanguagesFromOrigin($origin) as $language) {
            $urls[] = [
                'subject' => $this->parseService->parseSubject(
                    $this->pageRepository->getSubjectFromPageIdentifier((int)$origin, $language),
                    ['user' => $user]
                ),
                'url' => $this->getUrl($origin, $layout, $language),
            ];
        }
        return $urls;
    }

    protected function getUrlInDefaultInstallation(string $origin, string $layout): array
    {
        return [
            [
                'subject' => '',
                'url' => $this->getUrl($origin, $layout),
            ],
        ];
    }

    protected function getUrl(string $origin, string $layout, int $language = 0): string
    {
        // if (MathUtility::canBeInterpretedAsInteger($origin)) {
        //     return $this->getUrlFromPageIdentifier((int)$origin, $layout, $language);
        // }

        $url = '//' . GeneralUtility::getIndpEnv('HTTP_HOST') . '?type=1560777975';
        $url .= '&tx_luxletter_preview[origin]=' . htmlspecialchars($origin);
        $url .= '&tx_luxletter_preview[layout]=' . htmlspecialchars($layout);
        $url .= '&tx_luxletter_preview[language]=' . $language;
        return $url;
    }

    protected function getUrlFromPageIdentifier(int $origin, string $layout, int $language): string
    {
        return $this->siteService->getPageUrlFromParameter(
            $origin,
            [
                'type' => 1560777975,
                'tx_luxletter_preview' => [
                    'origin' => $origin,
                    'layout' => $layout,
                    'language' => $language,
                ],
            ]
        );
    }
}

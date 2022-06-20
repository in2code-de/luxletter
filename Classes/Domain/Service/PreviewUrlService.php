<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Service;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use In2code\Luxletter\Domain\Factory\UserFactory;
use In2code\Luxletter\Domain\Repository\PageRepository;
use In2code\Luxletter\Domain\Service\Parsing\Newsletter as NewsletterParsing;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class PreviewUrlService
 */
class PreviewUrlService
{
    /**
     * @var string
     */
    protected $origin = '';

    /**
     * @var string
     */
    protected $layout = '';

    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var NewsletterParsing
     */
    protected $parseService;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        $this->parseService = GeneralUtility::makeInstance(NewsletterParsing::class);
        $this->userFactory = GeneralUtility::makeInstance(UserFactory::class);
    }

    /**
     * @param string $origin
     * @param string $layout
     * @return array|string[]
     * @throws ExceptionDbalDriver
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
     * @throws ExceptionDbalDriver
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
                'url' => $this->getUrl($origin, $layout, $language)
            ];
        }
        return $urls;
    }

    /**
     * @param string $origin
     * @param string $layout
     * @return array
     */
    protected function getUrlInDefaultInstallation(string $origin, string $layout): array
    {
        return [
            [
                'subject' => '',
                'url' => $this->getUrl($origin, $layout)
            ]
        ];
    }

    /**
     * @param string $origin
     * @param string $layout
     * @param int $language
     * @return string
     */
    protected function getUrl(string $origin, string $layout, int $language = 0): string
    {
        $url = '//' . GeneralUtility::getIndpEnv('HTTP_HOST') . '?type=1560777975';
        $url .= '&tx_luxletter_fe[origin]=' . htmlspecialchars($origin);
        $url .= '&tx_luxletter_fe[layout]=' . htmlspecialchars($layout);
        $url .= '&tx_luxletter_fe[language]=' . $language;
        return $url;
    }
}

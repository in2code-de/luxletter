<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Mail;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use In2code\Luxletter\Domain\Factory\UserFactory;
use In2code\Luxletter\Domain\Repository\ConfigurationRepository;
use In2code\Luxletter\Domain\Repository\PageRepository;
use In2code\Luxletter\Domain\Service\Parsing\Newsletter as NewsletterParsing;
use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;
use In2code\Luxletter\Exception\ApiConnectionException;
use In2code\Luxletter\Exception\InvalidUrlException;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;

/**
 * Class TestMail
 */
class TestMail
{
    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var NewsletterParsing
     */
    protected $parseService;

    /**
     * @var ConfigurationRepository
     */
    protected $configurationRepository;

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
        $this->configurationRepository = GeneralUtility::makeInstance(ConfigurationRepository::class);
        $this->userFactory = GeneralUtility::makeInstance(UserFactory::class);
    }

    /**
     * @param string $origin
     * @param string $layout
     * @param int $configuration
     * @param string $subject
     * @param string $email
     * @return bool
     * @throws ApiConnectionException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws InvalidUrlException
     * @throws MisconfigurationException
     * @throws ExceptionDbalDriver
     */
    public function preflight(string $origin, string $layout, int $configuration, string $subject, string $email): bool
    {
        $status = false;
        $languages = $this->pageRepository->getLanguagesFromOrigin($origin);
        $configuration = $this->configurationRepository->findByUid($configuration);

        foreach ($languages as $language) {
            $parseUrlService = GeneralUtility::makeInstance(NewsletterUrl::class, $origin, $layout, $language)
                ->setModeTestmail();
            $user = $this->userFactory->getDummyUser();
            $mailService = GeneralUtility::makeInstance(
                SendMail::class,
                $this->parseService->parseSubject($this->getSubject($origin, $subject, $language), ['user' => $user]),
                $parseUrlService->getParsedContent($configuration->getSiteConfiguration()),
                $configuration
            );
            $status = $mailService->sendNewsletter([$email => $user->getReadableName()]);
            if ($status === false) {
                return $status;
            }
        }

        return $status;
    }

    /**
     * @param string $origin
     * @param string $subject
     * @param int $language
     * @return string
     * @throws ExceptionDbalDriver
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws MisconfigurationException
     */
    protected function getSubject(string $origin, string $subject, int $language): string
    {
        if (ConfigurationUtility::isMultiLanguageModeActivated()) {
            if (MathUtility::canBeInterpretedAsInteger($origin) === false) {
                throw new MisconfigurationException('Origin must be an integer', 1644166240);
            }
            return $this->pageRepository->getSubjectFromPageIdentifier((int)$origin, $language);
        }
        return $subject;
    }
}

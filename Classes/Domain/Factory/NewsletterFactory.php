<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Factory;

use DateTime;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Exception;
use In2code\Luxletter\Domain\Model\Configuration;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Repository\ConfigurationRepository;
use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Domain\Repository\PageRepository;
use In2code\Luxletter\Domain\Repository\UsergroupRepository;
use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;
use In2code\Luxletter\Exception\ApiConnectionException;
use In2code\Luxletter\Exception\InvalidUrlException;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\ConfigurationUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * NewsletterFactory
 */
class NewsletterFactory
{
    /**
     * Create a new newsletter and fill the queue
     *
     * @param string $title
     * @param int $usergroupIdentifier
     * @param int $configurationIdentifier
     * @param string $origin
     * @param int $language
     * @param string $layout
     * @param string $description
     * @param string $date Date format should be "2022-01-23T00:00"
     * @param string $subject Is only needed if extension runs NOT in multilanguage mode
     * @return Newsletter
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
     * @throws InvalidUrlException
     * @throws MisconfigurationException
     * @throws SiteNotFoundException
     * @throws ApiConnectionException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws Exception
     * @throws ExceptionDbalDriver
     */
    public function get(
        string $title,
        int $usergroupIdentifier,
        int $configurationIdentifier,
        string $origin,
        int $language,
        string $layout,
        string $description,
        string $date,
        string $subject
    ): Newsletter {
        if (ConfigurationUtility::isMultiLanguageModeActivated()) {
            $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
            $subject = $pageRepository->getSubjectFromPageIdentifier((int)$origin, $language);
        }
        $usergroupRepository = GeneralUtility::makeInstance(UsergroupRepository::class);
        $configurationRepository = GeneralUtility::makeInstance(ConfigurationRepository::class);
        /** @var Configuration $configuration */
        $configuration = $configurationRepository->findByUid($configurationIdentifier);
        $newsletterRepository = GeneralUtility::makeInstance(NewsletterRepository::class);

        $newsletter = GeneralUtility::makeInstance(Newsletter::class);
        $newsletter
            ->setTitle($title)
            ->setDescription($description)
            ->setSubject($subject)
            ->setReceiver($usergroupRepository->findByUid($usergroupIdentifier))
            ->setConfiguration($configuration)
            ->setOrigin($origin)
            ->setLanguage($language)
            ->setLayout($layout);

        $parseService = GeneralUtility::makeInstance(
            NewsletterUrl::class,
            $origin,
            $newsletter->getLayout(),
            $language
        );
        $newsletter->setBodytext($parseService->getParsedContent($configuration->getSiteConfiguration()));

        $dateTime = new DateTime();
        if ($date !== '') {
            $dateTime = new DateTime($date);
        }
        $newsletter->setDatetime($dateTime);
        $newsletterRepository->add($newsletter);
        $newsletterRepository->persistAll();
        return $newsletter;
    }
}

<?php

declare(strict_types=1);

namespace In2code\Luxletter\Domain\Factory;

use DateTime;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use In2code\Luxletter\Domain\Model\Category;
use In2code\Luxletter\Domain\Model\Configuration;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Repository\CategoryRepository;
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
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * NewsletterFactory
 */
class NewsletterFactory
{
    /**
     * @var PageRepository
     */
    protected $pageRepository;

    /**
     * @var ConfigurationRepository
     */
    protected $configurationRepository;

    /**
     * @var NewsletterRepository
     */
    protected $newsletterRepository;

    /**
     * @var UsergroupRepository
     */
    protected $usergroupRepository;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * @param PageRepository $pageRepository
     * @param ConfigurationRepository $configurationRepository
     * @param NewsletterRepository $newsletterRepository
     * @param UsergroupRepository $usergroupRepository
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        PageRepository $pageRepository,
        ConfigurationRepository $configurationRepository,
        NewsletterRepository $newsletterRepository,
        UsergroupRepository $usergroupRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->pageRepository = $pageRepository;
        $this->configurationRepository = $configurationRepository;
        $this->newsletterRepository = $newsletterRepository;
        $this->usergroupRepository = $usergroupRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Create a new newsletter and fill the queue
     *
     * @param string $title
     * @param array $usergroupIdentifiers
     * @param int $configurationIdentifier
     * @param string $origin
     * @param int $language
     * @param string $layout
     * @param int $categoryIdentifier
     * @param string $description
     * @param string $date Date format should be "2022-01-23T00:00"
     * @param string $subject Is only needed if extension runs NOT in multilanguage mode
     * @return Newsletter
     * @throws ApiConnectionException
     * @throws ExceptionDbalDriver
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
     * @throws InvalidQueryException
     * @throws InvalidUrlException
     * @throws MisconfigurationException
     * @throws SiteNotFoundException
     */
    public function get(
        string $title,
        array $usergroupIdentifiers,
        int $configurationIdentifier,
        string $origin,
        int $language,
        string $layout,
        int $categoryIdentifier,
        string $description,
        string $date,
        string $subject
    ): Newsletter {
        if (ConfigurationUtility::isMultiLanguageModeActivated()) {
            $subject = $this->pageRepository->getSubjectFromPageIdentifier((int)$origin, $language);
        }
        /** @var Configuration $configuration */
        $configuration = $this->configurationRepository->findByUid($configurationIdentifier);

        $newsletter = GeneralUtility::makeInstance(Newsletter::class);
        $newsletter
            ->setTitle($title)
            ->setDescription($description)
            ->setSubject($subject)
            ->setReceivers($this->getUsergroups($usergroupIdentifiers))
            ->setConfiguration($configuration)
            ->setOrigin($origin)
            ->setLanguage($language)
            ->setLayout($layout);
        if ($categoryIdentifier > 0) {
            /** @var Category $category */
            $category = $this->categoryRepository->findByUid($categoryIdentifier);
            $newsletter->setCategory($category);
        }

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
        $this->newsletterRepository->add($newsletter);
        $this->newsletterRepository->persistAll();
        return $newsletter;
    }

    /**
     * Convert queryresult to objectstorage
     *
     * @param array $usergroupIdentifiers
     * @return ObjectStorage
     * @throws InvalidQueryException
     */
    protected function getUsergroups(array $usergroupIdentifiers): ObjectStorage
    {
        $queryResult = $this->usergroupRepository->findByIdentifiers($usergroupIdentifiers);
        $objectStorage = GeneralUtility::makeInstance(ObjectStorage::class);
        if ($queryResult !== null) {
            foreach ($queryResult as $object) {
                $objectStorage->attach($object);
            }
        }
        return $objectStorage;
    }
}

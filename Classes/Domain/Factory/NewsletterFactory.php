<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Factory;

use DateTime;
use Exception;
use In2code\Luxletter\Domain\Model\Configuration;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Repository\ConfigurationRepository;
use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Domain\Repository\UsergroupRepository;
use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;
use In2code\Luxletter\Exception\InvalidUrlException;
use In2code\Luxletter\Exception\MisconfigurationException;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\Exception as ExbaseObjectException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * NewsletterFactory
 */
class NewsletterFactory
{
    /**
     * Create a new newsletter
     *
     * @param string $title
     * @param string $subject
     * @param int $usergroupIdentifier
     * @param int $configurationIdentifier
     * @param string $origin
     * @param string $layout
     * @param string $description
     * @param string $date Date format could be "2022-01-23T00:00"
     * @return Newsletter
     * @throws ExbaseObjectException
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws InvalidUrlException
     * @throws MisconfigurationException
     * @throws SiteNotFoundException
     * @throws Exception
     */
    public function get(
        string $title,
        string $subject,
        int $usergroupIdentifier,
        int $configurationIdentifier,
        string $origin,
        string $layout,
        string $description = '',
        string $date = ''
    ): Newsletter {
        $parseService = GeneralUtility::makeInstance(NewsletterUrl::class, $origin, $layout);
        $parseService->setParseVariables(false);

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
            ->setLayout($layout)
            ->setBodytext($parseService->getParsedContent($configuration->getSiteConfiguration()));
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

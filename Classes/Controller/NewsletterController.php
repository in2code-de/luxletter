<?php

declare(strict_types=1);
namespace In2code\Luxletter\Controller;

use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use Exception;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Luxletter\Domain\Model\Dto\Filter;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Repository\CategoryRepository;
use In2code\Luxletter\Domain\Repository\ConfigurationRepository;
use In2code\Luxletter\Domain\Repository\LogRepository;
use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Domain\Repository\PageRepository;
use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Domain\Service\LayoutService;
use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;
use In2code\Luxletter\Domain\Service\PreviewUrlService;
use In2code\Luxletter\Domain\Service\QueueService;
use In2code\Luxletter\Domain\Service\ReceiverAnalysisService;
use In2code\Luxletter\Exception\ApiConnectionException;
use In2code\Luxletter\Exception\AuthenticationFailedException;
use In2code\Luxletter\Exception\InvalidUrlException;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Mail\TestMail;
use In2code\Luxletter\Utility\BackendUserUtility;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\LocalizationUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class NewsletterController
 */
class NewsletterController extends ActionController
{
    /**
     * @var string
     */
    protected $wizardUserPreviewFile = 'EXT:luxletter/Resources/Private/Templates/Newsletter/WizardUserPreview.html';

    /**
     * @var string
     */
    protected $receiverDetailFile = 'EXT:luxletter/Resources/Private/Templates/Newsletter/ReceiverDetail.html';

    /**
     * @var NewsletterRepository|null
     */
    protected $newsletterRepository = null;

    /**
     * @var UserRepository|null
     */
    protected $userRepository = null;

    /**
     * @var LogRepository|null
     */
    protected $logRepository = null;

    /**
     * @var ConfigurationRepository|null
     */
    protected $configurationRepository = null;

    /**
     * @var PageRepository|null
     */
    protected $pageRepository = null;

    /**
     * @var LayoutService|null
     */
    protected $layoutService = null;

    /**
     * @var CategoryRepository|null
     */
    protected $categoryRepository = null;

    /**
     * @param NewsletterRepository $newsletterRepository
     * @param UserRepository $userRepository
     * @param LogRepository $logRepository
     * @param ConfigurationRepository $configurationRepository
     * @param PageRepository $pageRepository
     * @param LayoutService $layoutService
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        NewsletterRepository $newsletterRepository,
        UserRepository $userRepository,
        LogRepository $logRepository,
        ConfigurationRepository $configurationRepository,
        PageRepository $pageRepository,
        LayoutService $layoutService,
        CategoryRepository $categoryRepository
    ) {
        $this->newsletterRepository = $newsletterRepository;
        $this->userRepository = $userRepository;
        $this->logRepository = $logRepository;
        $this->configurationRepository = $configurationRepository;
        $this->pageRepository = $pageRepository;
        $this->layoutService = $layoutService;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @return void
     * @throws DBALException
     * @throws ExceptionDbalDriver
     * @throws ExceptionDbal
     * @noinspection PhpUnused
     */
    public function dashboardAction(): void
    {
        $this->view->assignMultiple(
            [
                'statistic' => [
                    'overallReceivers' => $this->logRepository->getNumberOfReceivers(),
                    'overallOpenings' => $this->logRepository->getOverallOpenings(),
                    'openingsByClickers' => $this->logRepository->getOpeningsByClickers(),
                    'overallClicks' => $this->logRepository->getOverallClicks(),
                    'overallUnsubscribes' => $this->logRepository->getOverallUnsubscribes(),
                    'overallMailsSent' => $this->logRepository->getOverallMailsSent(),
                    'overallOpenRate' => $this->logRepository->getOverallOpenRate(),
                    'overallClickRate' => $this->logRepository->getOverallClickRate(),
                    'overallUnsubscribeRate' => $this->logRepository->getOverallUnsubscribeRate(),
                ],
                'groupedLinksByHref' => $this->logRepository->getGroupedLinksByHref(),
                'newsletters' => $this->newsletterRepository->findAll()->getQuery()->setLimit(10)->execute(),
            ]
        );
    }

    /**
     * @return void
     */
    public function listAction(): void
    {
        $this->view->assignMultiple([
            'newsletters' => $this->newsletterRepository->findAll(),
            'newslettersGrouped' => $this->newsletterRepository->findAllGroupedByCategories(),
            'configurations' => $this->configurationRepository->findAll(),
        ]);
    }

    /**
     * @return void
     * @throws InvalidConfigurationTypeException
     * @throws ExceptionDbalDriver
     * @noinspection PhpUnused
     */
    public function newAction(): void
    {
        $this->view->assignMultiple([
            'configurations' => $this->configurationRepository->findAll(),
            'layouts' => $this->layoutService->getLayouts(),
            'newsletterpages' => $this->pageRepository->findAllNewsletterPages(),
            'categories' => $this->categoryRepository->findAllLuxletterCategories(),
        ]);
    }

    /**
     * @return void
     * @throws NoSuchArgumentException
     * @noinspection PhpUnused
     */
    public function initializeCreateAction(): void
    {
        $this->prepareArgumentsForPersistence();
    }

    /**
     * @param Newsletter $newsletter
     * @return void
     * @throws ApiConnectionException
     * @throws ExceptionDbalDriver
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
     * @throws InvalidUrlException
     * @throws MisconfigurationException
     * @throws SiteNotFoundException
     * @throws StopActionException
     */
    public function createAction(Newsletter $newsletter): void
    {
        $languages = $this->pageRepository->getLanguagesFromOrigin($newsletter->getOrigin());
        foreach ($languages as $language) {
            $newsletterLanguage = clone $newsletter;
            $this->setBodytextInNewsletter($newsletterLanguage, $language);
            $newsletterLanguage->setLanguage($language);
            if (ConfigurationUtility::isMultiLanguageModeActivated()) {
                $newsletterLanguage->setSubject(
                    $this->pageRepository->getSubjectFromPageIdentifier(
                        (int)$newsletterLanguage->getOrigin(),
                        $language
                    )
                );
            }
            $this->newsletterRepository->add($newsletterLanguage);
            $this->newsletterRepository->persistAll();
            $queueService = GeneralUtility::makeInstance(QueueService::class);
            $queueService->addMailReceiversToQueue($newsletterLanguage, $language);
        }
        $this->addFlashMessage(LocalizationUtility::translate('module.newsletter.create.message'));
        $this->redirect('list');
    }

    /**
     * @param Newsletter $newsletter
     * @return void
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     * @throws UnknownObjectException
     */
    public function disableAction(Newsletter $newsletter): void
    {
        $newsletter->disable();
        $this->newsletterRepository->update($newsletter);
        $this->redirect('list');
    }

    /**
     * @param Newsletter $newsletter
     * @return void
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     * @throws UnknownObjectException
     * @noinspection PhpUnused
     */
    public function enableAction(Newsletter $newsletter): void
    {
        $newsletter->enable();
        $this->newsletterRepository->update($newsletter);
        $this->redirect('list');
    }

    /**
     * @param Newsletter $newsletter
     * @return void
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     * @throws DBALException
     */
    public function deleteAction(Newsletter $newsletter): void
    {
        $this->newsletterRepository->removeNewsletterAndQueues($newsletter);
        $this->addFlashMessage(LocalizationUtility::translate('module.newsletter.delete.message'));
        $this->redirect('list');
    }

    /**
     * Always pass a filter to receiverAction. If filter is given, save in session.
     *
     * @return void
     * @throws NoSuchArgumentException
     * @throws InvalidArgumentNameException
     */
    public function initializeReceiverAction(): void
    {
        $filterArgument = $this->arguments->getArgument('filter');
        $filterPropertyMapping = $filterArgument->getPropertyMappingConfiguration();
        $filterPropertyMapping->allowAllProperties();
        if ($this->request->hasArgument('filter') === false) {
            $filter = BackendUserUtility::getSessionValue('filter');
        } else {
            $filter = (array)$this->request->getArgument('filter');
            BackendUserUtility::saveValueToSession('filter', $filter);
        }
        if (isset($filter['usergroup']['__identity']) && $filter['usergroup']['__identity'] === '0') {
            unset($filter['usergroup']);
        }
        $this->request->setArgument('filter', $filter);
    }

    /**
     * @param Filter $filter
     * @return void
     * @throws DBALException
     * @throws ExceptionDbalDriver
     * @throws InvalidQueryException
     * @noinspection PhpUnused
     */
    public function receiverAction(Filter $filter): void
    {
        $receiverAnalysisService = GeneralUtility::makeInstance(ReceiverAnalysisService::class);
        $users = $this->userRepository->getUsersByFilter($filter);
        $this->view->assignMultiple(
            [
                'filter' => $filter,
                'users' => $users,
                'activities' => $receiverAnalysisService->getActivitiesStatistic($users),
            ]
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws DBALException
     * @throws ExceptionDbalDriver
     * @noinspection PhpUnused
     */
    public function wizardUserPreviewAjax(ServerRequestInterface $request): ResponseInterface
    {
        $userRepository = GeneralUtility::makeInstance(UserRepository::class);
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->wizardUserPreviewFile));
        $standaloneView->assignMultiple([
            'userPreview' => $userRepository->getUsersFromGroup((int)$request->getQueryParams()['usergroup'], -1, 3),
            'userAmount' => $userRepository->getUserAmountFromGroup((int)$request->getQueryParams()['usergroup']),
        ]);
        $response = ObjectUtility::getJsonResponse();
        $response->getBody()->write(json_encode(
            ['html' => $standaloneView->render()]
        ));
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws ApiConnectionException
     * @throws AuthenticationFailedException
     * @throws ExceptionDbalDriver
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws InvalidUrlException
     * @throws MisconfigurationException
     * @noinspection PhpUnused
     */
    public function testMailAjax(ServerRequestInterface $request): ResponseInterface
    {
        if (BackendUserUtility::isBackendUserAuthenticated() === false) {
            throw new AuthenticationFailedException('You are not authenticated to send mails', 1560872725);
        }
        $testMail = GeneralUtility::makeInstance(TestMail::class);
        $status = $testMail->preflight(
            $request->getQueryParams()['origin'],
            $request->getQueryParams()['layout'],
            (int)$request->getQueryParams()['configuration'],
            $request->getQueryParams()['subject'],
            $request->getQueryParams()['email']
        );
        $response = ObjectUtility::getJsonResponse();
        $response->getBody()->write(json_encode(['status' => $status]));
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws AuthenticationFailedException
     * @throws ExceptionDbalDriver
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws MisconfigurationException
     * @throws InvalidConfigurationTypeException
     * @noinspection PhpUnused
     */
    public function previewSourcesAjax(ServerRequestInterface $request): ResponseInterface
    {
        if (BackendUserUtility::isBackendUserAuthenticated() === false) {
            throw new AuthenticationFailedException('You are not authenticated to send mails', 1645707268);
        }
        $previewUrlService = GeneralUtility::makeInstance(PreviewUrlService::class);
        $response = ObjectUtility::getJsonResponse();
        $content = $previewUrlService->get($request->getQueryParams()['origin'], $request->getQueryParams()['layout']);
        $response->getBody()->write(json_encode($content));
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @noinspection PhpUnused
     */
    public function receiverDetailAjax(ServerRequestInterface $request): ResponseInterface
    {
        $userRepository = GeneralUtility::makeInstance(UserRepository::class);
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $logRepository = GeneralUtility::makeInstance(LogRepository::class);
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->receiverDetailFile));
        $user = $userRepository->findByUid((int)$request->getQueryParams()['user']);
        $standaloneView->assignMultiple([
            'user' => $user,
            'visitor' => $visitorRepository->findOneByFrontenduser($user),
            'logs' => $logRepository->findByUser($user),
        ]);
        $response = ObjectUtility::getJsonResponse();
        $response->getBody()->write(json_encode(
            ['html' => $standaloneView->render()]
        ));
        return $response;
    }

    /**
     * @return void
     * @throws NoSuchArgumentException
     * @throws Exception
     */
    protected function prepareArgumentsForPersistence(): void
    {
        if ($this->request->hasArgument('newsletter')) {
            $newsletter = (array)$this->request->getArgument('newsletter');

            // DateTime
            $datetime = new DateTime();
            if (!empty($newsletter['datetime'])) {
                $datetime = new DateTime($newsletter['datetime']);
            }
            $newsletter['datetime'] = $datetime;

            // Category
            if (isset($newsletter['category']) && $newsletter['category'] === '0') {
                unset($newsletter['category']);
            }

            $this->request->setArgument('newsletter', $newsletter);
        }
    }

    /**
     * @param Newsletter $newsletter
     * @param int $language
     * @return void
     * @throws ApiConnectionException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws InvalidUrlException
     * @throws MisconfigurationException
     * @throws SiteNotFoundException
     */
    protected function setBodytextInNewsletter(Newsletter $newsletter, int $language): void
    {
        $parseService = GeneralUtility::makeInstance(
            NewsletterUrl::class,
            $newsletter->getOrigin(),
            $newsletter->getLayout(),
            $language
        );
        $bodytext = $parseService->getParsedContent($newsletter->getConfiguration()->getSiteConfiguration());
        $newsletter->setBodytext($bodytext);
    }
}

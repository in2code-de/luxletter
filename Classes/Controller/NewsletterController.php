<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Controller;

use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use Exception;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Luxletter\Domain\Factory\UserFactory;
use In2code\Luxletter\Domain\Model\Dto\Filter;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Repository\ConfigurationRepository;
use In2code\Luxletter\Domain\Repository\LogRepository;
use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Domain\Service\LayoutService;
use In2code\Luxletter\Domain\Service\Parsing\Newsletter as NewsletterParsing;
use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;
use In2code\Luxletter\Domain\Service\QueueService;
use In2code\Luxletter\Domain\Service\ReceiverAnalysisService;
use In2code\Luxletter\Exception\AuthenticationFailedException;
use In2code\Luxletter\Exception\InvalidUrlException;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Mail\SendMail;
use In2code\Luxletter\Signal\SignalTrait;
use In2code\Luxletter\Utility\BackendUserUtility;
use In2code\Luxletter\Utility\LocalizationUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Object\Exception as ExceptionExtbaseObject;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class NewsletterController
 */
class NewsletterController extends ActionController
{
    use SignalTrait;

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
     * @var LayoutService|null
     */
    protected $layoutService = null;

    /**
     * @param NewsletterRepository $newsletterRepository
     * @param UserRepository $userRepository
     * @param LogRepository $logRepository
     * @param ConfigurationRepository $configurationRepository
     * @param LayoutService $layoutService
     */
    public function __construct(
        NewsletterRepository $newsletterRepository,
        UserRepository $userRepository,
        LogRepository $logRepository,
        ConfigurationRepository $configurationRepository,
        LayoutService $layoutService
    ) {
        $this->newsletterRepository = $newsletterRepository;
        $this->userRepository = $userRepository;
        $this->logRepository = $logRepository;
        $this->configurationRepository = $configurationRepository;
        $this->layoutService = $layoutService;
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
                    'overallClicks' => $this->logRepository->getOverallClicks(),
                    'overallUnsubscribes' => $this->logRepository->getOverallUnsubscribes(),
                    'overallMailsSent' => $this->logRepository->getOverallMailsSent(),
                    'overallOpenRate' => $this->logRepository->getOverallOpenRate(),
                    'overallClickRate' => $this->logRepository->getOverallClickRate(),
                    'overallUnsubscribeRate' => $this->logRepository->getOverallUnsubscribeRate()
                ],
                'groupedLinksByHref' => $this->logRepository->getGroupedLinksByHref(),
                'newsletters' => $this->newsletterRepository->findAll()->getQuery()->setLimit(10)->execute()
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
            'configurations' => $this->configurationRepository->findAll()
        ]);
    }

    /**
     * @return void
     * @throws InvalidConfigurationTypeException
     * @noinspection PhpUnused
     */
    public function newAction(): void
    {
        $this->view->assignMultiple([
            'configurations' => $this->configurationRepository->findAll(),
            'layouts' => $this->layoutService->getLayouts()
        ]);
    }

    /**
     * @return void
     * @throws ExceptionExtbaseObject
     * @throws InvalidConfigurationTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws InvalidUrlException
     * @throws MisconfigurationException
     * @throws NoSuchArgumentException
     * @throws InvalidArgumentNameException
     * @noinspection PhpUnused
     */
    public function initializeCreateAction(): void
    {
        $this->setDatetimeObjectInNewsletterRequest();
        $this->parseNewsletterToBodytext();
    }

    /**
     * @param Newsletter $newsletter
     * @return void
     * @throws ExceptionExtbaseObject
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws StopActionException
     * @throws ExceptionDbalDriver
     */
    public function createAction(Newsletter $newsletter): void
    {
        $this->newsletterRepository->add($newsletter);
        $this->newsletterRepository->persistAll();
        $queueService = GeneralUtility::makeInstance(QueueService::class);
        $queueService->addMailReceiversToQueue($newsletter);
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
                'activities' => $receiverAnalysisService->getActivitiesStatistic($users)
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
            'userPreview' => $userRepository->getUsersFromGroup((int)$request->getQueryParams()['usergroup'], 3),
            'userAmount' => $userRepository->getUserAmountFromGroup((int)$request->getQueryParams()['usergroup'])
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
     * @throws AuthenticationFailedException
     * @throws ExceptionExtbaseObject
     * @throws InvalidConfigurationTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws InvalidUrlException
     * @throws MisconfigurationException
     * @noinspection PhpUnused
     */
    public function testMailAjax(ServerRequestInterface $request): ResponseInterface
    {
        if (BackendUserUtility::isBackendUserAuthenticated() === false) {
            throw new AuthenticationFailedException('You are not authenticated to send mails', 1560872725);
        }
        $parseUrlService = GeneralUtility::makeInstance(
            NewsletterUrl::class,
            $request->getQueryParams()['origin'],
            $request->getQueryParams()['layout']
        )->setModeTestmail();
        $parseService = GeneralUtility::makeInstance(NewsletterParsing::class);
        $configurationRepository = GeneralUtility::makeInstance(ConfigurationRepository::class);
        $configuration = $configurationRepository->findByUid($request->getQueryParams()['configuration']);
        $userFactory = GeneralUtility::makeInstance(UserFactory::class);
        $user = $userFactory->getDummyUser();
        $mailService = GeneralUtility::makeInstance(
            SendMail::class,
            $parseService->parseSubject(
                $request->getQueryParams()['subject'],
                ['user' => $user]
            ),
            $parseUrlService->getParsedContent($configuration->getSiteConfiguration()),
            $configuration
        );
        $status = $mailService->sendNewsletter([$request->getQueryParams()['email'] => $user->getReadableName()]);
        $response = ObjectUtility::getJsonResponse();
        $response->getBody()->write(json_encode(['status' => $status]));
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
            'logs' => $logRepository->findByUser($user)
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
    protected function setDatetimeObjectInNewsletterRequest(): void
    {
        $newsletter = (array)$this->request->getArgument('newsletter');
        if (!empty($newsletter['datetime'])) {
            $datetime = new DateTime($newsletter['datetime']);
        } else {
            $datetime = new DateTime();
        }
        $newsletter['datetime'] = $datetime;
        $this->request->setArgument('newsletter', $newsletter);
    }

    /**
     * @return void
     * @throws ExceptionExtbaseObject
     * @throws InvalidConfigurationTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws InvalidUrlException
     * @throws MisconfigurationException
     * @throws NoSuchArgumentException
     * @throws InvalidArgumentNameException
     */
    protected function parseNewsletterToBodytext(): void
    {
        $newsletter = (array)$this->request->getArgument('newsletter');
        $parseService = GeneralUtility::makeInstance(
            NewsletterUrl::class,
            $newsletter['origin'],
            $newsletter['layout']
        );
        $configuration = $this->configurationRepository->findByUid((int)$newsletter['configuration']);
        $newsletter['bodytext'] = $parseService->getParsedContent($configuration->getSiteConfiguration());
        $this->request->setArgument('newsletter', $newsletter);
    }
}

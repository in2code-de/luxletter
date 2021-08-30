<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Controller;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Luxletter\Domain\Factory\UserFactory;
use In2code\Luxletter\Domain\Model\Configuration;
use In2code\Luxletter\Domain\Model\Dto\Filter;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\ConfigurationRepository;
use In2code\Luxletter\Domain\Repository\LogRepository;
use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Domain\Service\ParseNewsletterService;
use In2code\Luxletter\Domain\Service\ParseNewsletterUrlService;
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
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Object\Exception;
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
     * @var NewsletterRepository
     */
    protected $newsletterRepository = null;

    /**
     * @var UserRepository
     */
    protected $userRepository = null;

    /**
     * @var LogRepository
     */
    protected $logRepository = null;

    /**
     * @var ConfigurationRepository
     */
    protected $configurationRepository = null;

    /**
     * NewsletterController constructor.
     * @param NewsletterRepository|null $newsletterRepository
     * @param UserRepository|null $userRepository
     * @param LogRepository|null $logRepository
     * @param ConfigurationRepository|null $configurationRepository
     * @throws Exception
     */
    public function __construct(
        NewsletterRepository $newsletterRepository = null,
        UserRepository $userRepository = null,
        LogRepository $logRepository = null,
        ConfigurationRepository $configurationRepository = null
    ) {
        $this->newsletterRepository = $newsletterRepository ?: ObjectUtility::getObjectManager()->get(
            NewsletterRepository::class
        );
        $this->userRepository = $userRepository ?: ObjectUtility::getObjectManager()->get(
            UserRepository::class
        );
        $this->logRepository = $logRepository ?: ObjectUtility::getObjectManager()->get(LogRepository::class);
        $this->configurationRepository = $configurationRepository ?: ObjectUtility::getObjectManager()->get(
            ConfigurationRepository::class
        );
    }

    /**
     * @return void
     * @throws DBALException
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
     */
    public function newAction(): void
    {
        $this->view->assignMultiple([
            'configurations' => $this->configurationRepository->findAll()
        ]);
    }

    /**
     * @return void
     * @throws Exception
     * @throws InvalidArgumentNameException
     * @throws InvalidConfigurationTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws InvalidUrlException
     * @throws MisconfigurationException
     * @throws NoSuchArgumentException
     */
    public function initializeCreateAction(): void
    {
        $this->setDatetimeObjectInNewsletterRequest();
        $this->parseNewsletterToBodytext();
    }

    /**
     * @param Newsletter $newsletter
     * @return void
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws StopActionException
     */
    public function createAction(Newsletter $newsletter): void
    {
        $this->newsletterRepository->add($newsletter);
        $this->newsletterRepository->persistAll();
        $queueService = ObjectUtility::getObjectManager()->get(QueueService::class);
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
     * @throws InvalidArgumentNameException
     * @throws NoSuchArgumentException
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
        $this->request->setArgument('filter', $filter);
    }

    /**
     * @param Filter $filter
     * @return void
     * @throws InvalidQueryException
     * @throws DBALException
     * @throws Exception
     */
    public function receiverAction(Filter $filter): void
    {
        $receiverAnalysisService = ObjectUtility::getObjectManager()->get(ReceiverAnalysisService::class);
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
     * @throws Exception
     */
    public function wizardUserPreviewAjax(ServerRequestInterface $request): ResponseInterface
    {
        $userRepository = ObjectUtility::getObjectManager()->get(UserRepository::class);
        $standaloneView = ObjectUtility::getObjectManager()->get(StandaloneView::class);
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
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws InvalidUrlException
     * @throws MisconfigurationException
     * @throws TransportExceptionInterface
     */
    public function testMailAjax(ServerRequestInterface $request): ResponseInterface
    {
        if (BackendUserUtility::isBackendUserAuthenticated() === false) {
            throw new AuthenticationFailedException('You are not authenticated to send mails', 1560872725);
        }
        /** @var ParseNewsletterUrlService $parseUrlService */
        $parseUrlService = ObjectUtility::getObjectManager()->get(
            ParseNewsletterUrlService::class,
            $request->getQueryParams()['origin']
        );
        /** @var ParseNewsletterService $parseService */
        $parseService = ObjectUtility::getObjectManager()->get(ParseNewsletterService::class);
        /** @var ConfigurationRepository $configurationRepository */
        $configurationRepository = ObjectUtility::getObjectManager()->get(ConfigurationRepository::class);
        /** @var Configuration $configuration */
        $configuration = $configurationRepository->findByUid($request->getQueryParams()['configuration']);
        /** @var UserFactory $userFactory */
        $userFactory = ObjectUtility::getObjectManager()->get(UserFactory::class);
        $user = $userFactory->getDummyUser();
        $mailService = ObjectUtility::getObjectManager()->get(
            SendMail::class,
            $parseService->parseMailText(
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
     * @throws Exception
     */
    public function receiverDetailAjax(ServerRequestInterface $request): ResponseInterface
    {
        $userRepository = ObjectUtility::getObjectManager()->get(UserRepository::class);
        $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
        $logRepository = ObjectUtility::getObjectManager()->get(LogRepository::class);
        $standaloneView = ObjectUtility::getObjectManager()->get(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->receiverDetailFile));
        /** @var User $user */
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
     * @throws InvalidArgumentNameException
     * @throws NoSuchArgumentException
     * @throws \Exception
     */
    protected function setDatetimeObjectInNewsletterRequest(): void
    {
        $newsletter = (array)$this->request->getArgument('newsletter');
        if (!empty($newsletter['datetime'])) {
            $datetime = \DateTime::createFromFormat('H:i d-m-Y', $newsletter['datetime']);
        } else {
            $datetime = new \DateTime();
        }
        $newsletter['datetime'] = $datetime;
        $this->request->setArgument('newsletter', $newsletter);
    }

    /**
     * @return void
     * @throws Exception
     * @throws InvalidArgumentNameException
     * @throws InvalidConfigurationTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws InvalidUrlException
     * @throws MisconfigurationException
     * @throws NoSuchArgumentException
     */
    protected function parseNewsletterToBodytext(): void
    {
        $newsletter = (array)$this->request->getArgument('newsletter');
        /** @var ParseNewsletterUrlService $parseService */
        $parseService = ObjectUtility::getObjectManager()->get(ParseNewsletterUrlService::class, $newsletter['origin']);
        $parseService->setParseVariables(false);
        /** @var Configuration $configuration */
        $configuration = $this->configurationRepository->findByUid((int)$newsletter['configuration']);
        $newsletter['bodytext'] = $parseService->getParsedContent($configuration->getSiteConfiguration());
        $this->request->setArgument('newsletter', $newsletter);
    }
}

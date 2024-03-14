<?php

declare(strict_types=1);
namespace In2code\Luxletter\Controller;

use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Luxletter\Domain\Model\Dto\Filter;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Repository\LogRepository;
use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Domain\Service\PreviewUrlService;
use In2code\Luxletter\Domain\Service\QueueService;
use In2code\Luxletter\Domain\Service\ReceiverAnalysisService;
use In2code\Luxletter\Events\AfterTestMailButtonClickedEvent;
use In2code\Luxletter\Exception\AuthenticationFailedException;
use In2code\Luxletter\Mail\TestMail;
use In2code\Luxletter\Utility\BackendUserUtility;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\LocalizationUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class NewsletterController extends AbstractNewsletterController
{
    public function initializeDashboardAction(): void
    {
        $this->setFilter();
    }

    public function dashboardAction(Filter $filter): ResponseInterface
    {
        $this->view->assignMultiple([
            'filter' => $filter,
            'newsletters' => $this->newsletterRepository->findAllByFilter($filter->setLimit(10)),
            'groupedLinksByHref' => $this->logRepository->getGroupedLinksByHref($filter->setLimit(8)),
            'statistic' => [
                'overallReceivers' => $this->logRepository->getNumberOfReceivers($filter),
                'overallMailsSent' => $this->logRepository->getOverallMailsSent($filter),
                'overallOpenings' => $this->logRepository->getOverallOpenings($filter),
                'overallNonOpenings' => $this->logRepository->getOverallNonOpenings($filter),
                'overallOpenRate' => $this->logRepository->getOverallOpenRate($filter),
                'overallNonClicks' => $this->logRepository->getOverallNonClicks($filter),
                'openingsByClickers' => $this->logRepository->getOpeningsByClickers($filter),
                'overallClickRate' => $this->logRepository->getOverallClickRate($filter),
                'overallUnsubscribeRate' => $this->logRepository->getOverallUnsubscribeRate($filter),
                'overallUnsubscribes' => $this->logRepository->getOverallUnsubscribes($filter),
                'overallSubscribes' => $this->logRepository->getOverallSubscribes($filter),
            ],
        ]);

        $this->addDocumentHeaderForNewsletterController();
        return $this->defaultRendering();
    }

    public function initializeListAction(): void
    {
        $this->setFilter();
    }

    public function listAction(Filter $filter): ResponseInterface
    {
        $this->view->assignMultiple([
            'filter' => $filter,
            'newsletters' => $this->newsletterRepository->findAllAuthorized($filter),
            'newslettersGrouped' => $this->newsletterRepository->findAllGroupedByCategories($filter),
            'configurations' => $this->configurationRepository->findAllAuthorized(),
            'categories' => $this->categoryRepository->findAllLuxletterCategories(),
        ]);

        $this->addDocumentHeaderForNewsletterController();
        return $this->defaultRendering();
    }

    public function resetFilterAction(string $redirectAction): ResponseInterface
    {
        BackendUserUtility::saveValueToSession('filter', $redirectAction, $this->getControllerName(), []);
        return $this->redirect($redirectAction);
    }

    public function editAction(Newsletter $newsletter): ResponseInterface
    {
        if ($newsletter->canBeRead() === false) {
            throw new AuthenticationFailedException('You are not allowed to see this record', 1709329205);
        }

        $this->view->assignMultiple([
            'newsletter' => $newsletter,
            'configurations' => $this->configurationRepository->findAllAuthorized(),
            'layouts' => $this->layoutService->getLayouts(),
            'newsletterpages' => $this->pageRepository->findAllNewsletterPages(),
            'categories' => $this->categoryRepository->findAllLuxletterCategories(),
            'usergroups' => $this->usergroupRepository->getReceiverGroups(),
        ]);

        $this->addDocumentHeaderForNewsletterController();
        return $this->defaultRendering();
    }

    public function initializeUpdateAction(): void
    {
        $this->prepareArgumentsForPersistence();
    }

    public function updateAction(Newsletter $newsletter): ResponseInterface
    {
        if ($newsletter->canBeRead() === false) {
            throw new AuthenticationFailedException('You are not allowed to see this record', 1709329247);
        }

        $this->setBodytextInNewsletter($newsletter, $newsletter->getLanguage());
        if (ConfigurationUtility::isMultiLanguageModeActivated()) {
            $newsletter->setSubject(
                $this->pageRepository->getSubjectFromPageIdentifier(
                    (int)$newsletter->getOrigin(),
                    $newsletter->getLanguage()
                )
            );
        }
        $this->newsletterRepository->update($newsletter);
        $this->newsletterRepository->persistAll();
        $this->addFlashMessage(LocalizationUtility::translate('module.newsletter.update.message'));
        return $this->redirect('list');
    }

    public function newAction(): ResponseInterface
    {
        $this->view->assignMultiple([
            'configurations' => $this->configurationRepository->findAllAuthorized(),
            'layouts' => $this->layoutService->getLayouts(),
            'newsletterpages' => $this->pageRepository->findAllNewsletterPages(),
            'categories' => $this->categoryRepository->findAllLuxletterCategories(),
            'usergroups' => $this->usergroupRepository->getReceiverGroups(),
        ]);

        $this->addDocumentHeaderForNewsletterController();
        return $this->defaultRendering();
    }

    public function initializeCreateAction(): void
    {
        $this->prepareArgumentsForPersistence();
    }

    public function createAction(Newsletter $newsletter): ResponseInterface
    {
        if ($newsletter->canBeRead() === false) {
            throw new AuthenticationFailedException('You are not allowed to see this record', 1709329276);
        }

        $languages = $this->pageRepository->getLanguagesFromOrigin($newsletter->getOrigin());
        foreach ($languages as $language) {
            $newsletterLanguage = clone $newsletter;
            $this->setBodytextInNewsletter($newsletterLanguage, $language);
            $newsletterLanguage->setLanguage($language);
            $receivers = clone $newsletter->getReceivers();
            $newsletterLanguage->setReceivers($receivers);
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

            if (ConfigurationUtility::isAsynchronousQueueStorageActivated() === false) {
                $queueService = GeneralUtility::makeInstance(QueueService::class);
                $queueService->addMailReceiversToQueue($newsletterLanguage, $language);
            }
        }

        $this->addFlashMessage(LocalizationUtility::translate('module.newsletter.create.message'));
        return $this->redirect('list');
    }

    public function disableAction(Newsletter $newsletter): ResponseInterface
    {
        if ($newsletter->canBeRead() === false) {
            throw new AuthenticationFailedException('You are not allowed to see this record', 1709329304);
        }

        $newsletter->disable();
        $this->newsletterRepository->update($newsletter);
        return $this->redirect('list');
    }

    public function enableAction(Newsletter $newsletter): ResponseInterface
    {
        if ($newsletter->canBeRead() === false) {
            throw new AuthenticationFailedException('You are not allowed to see this record', 1709329338);
        }

        $newsletter->enable();
        $this->newsletterRepository->update($newsletter);
        return $this->redirect('list');
    }

    public function deleteAction(Newsletter $newsletter): ResponseInterface
    {
        if ($newsletter->canBeRead() === false) {
            throw new AuthenticationFailedException('You are not allowed to see this record', 1709329345);
        }

        $this->newsletterRepository->removeNewsletterAndQueues($newsletter);
        $this->addFlashMessage(LocalizationUtility::translate('module.newsletter.delete.message'));
        return $this->redirect('list');
    }

    public function initializeReceiverAction(): void
    {
        $this->setFilter();
    }

    public function receiverAction(Filter $filter): ResponseInterface
    {
        $receiverAnalysisService = GeneralUtility::makeInstance(ReceiverAnalysisService::class);
        $users = $this->userRepository->getUsersByFilter($filter->setLimit(1000));
        $this->view->assignMultiple(
            [
                'filter' => $filter,
                'users' => $users,
                'activities' => $receiverAnalysisService->getActivitiesStatistic($users),
                'usergroups' => $this->usergroupRepository->getReceiverGroups(),
            ]
        );

        $this->addDocumentHeaderForNewsletterController();
        return $this->defaultRendering();
    }

    public function wizardUserPreviewAjax(ServerRequestInterface $request): ResponseInterface
    {
        $usergroups = GeneralUtility::intExplode(',', $request->getQueryParams()['usergroups'], true);
        $userRepository = GeneralUtility::makeInstance(UserRepository::class);
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->wizardUserPreviewFile));
        $standaloneView->assignMultiple([
            'userPreview' => $userRepository->getUsersFromGroups($usergroups, -1, 3),
            'userAmount' => $userRepository->getUserAmountFromGroups($usergroups),
        ]);
        $response = ObjectUtility::getJsonResponse();
        $response->getBody()->write(json_encode(
            ['html' => $standaloneView->render()]
        ));
        return $response;
    }

    public function testMailAjax(ServerRequestInterface $request): ResponseInterface
    {
        if (BackendUserUtility::isBackendUserAuthenticated() === false) {
            throw new AuthenticationFailedException('You are not authenticated to send mails', 1560872725);
        }
        $status = null;

        /**
         * This event can be used for sending the Test-email with external logic
         * @see Documentation/Tech/Events.md
         */
        $event = GeneralUtility::makeInstance(AfterTestMailButtonClickedEvent::class, $request);
        $this->eventDispatcher->dispatch($event);

        if ($event->isTestMailIsSendExternal() === false) {
            $testMail = GeneralUtility::makeInstance(TestMail::class);
            $status = $testMail->preflight(
                $request->getQueryParams()['origin'],
                $request->getQueryParams()['layout'],
                (int)$request->getQueryParams()['configuration'],
                $request->getQueryParams()['subject'],
                $request->getQueryParams()['email']
            );
        }
        $responseData = [
            'status' => $status ?? $event->getStatus(),
        ];
        if ($event->isTestMailIsSendExternal()) {
            $responseData += $event->getStatusResponse();
        }
        $response = ObjectUtility::getJsonResponse();
        $response->getBody()->write(json_encode($responseData, JSON_THROW_ON_ERROR));
        return $response;
    }

    public function previewSourcesAjax(ServerRequestInterface $request): ResponseInterface
    {
        if (BackendUserUtility::isBackendUserAuthenticated() === false) {
            throw new AuthenticationFailedException('You are not authenticated to send mails', 1645707268);
        }
        $previewUrlService = GeneralUtility::makeInstance(PreviewUrlService::class);
        $content = $previewUrlService->get($request->getQueryParams()['origin'], $request->getQueryParams()['layout']);
        $response = ObjectUtility::getJsonResponse();
        $response->getBody()->write(json_encode($content));
        return $response;
    }

    public function receiverDetailAjax(ServerRequestInterface $request): ResponseInterface
    {
        $userRepository = GeneralUtility::makeInstance(UserRepository::class);
        $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
        $logRepository = GeneralUtility::makeInstance(LogRepository::class);
        $standaloneView = GeneralUtility::makeInstance(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->receiverDetailFile));
        $user = $userRepository->findByUid((int)$request->getQueryParams()['user']);
        $visitor = $visitorRepository->findOneByFrontenduser($user);
        $variables = [
            'user' => $user,
        ];
        if ($visitor !== null && $visitor->canBeRead()) {
            $variables += [
                'visitor' => $visitor,
                'logs' => $logRepository->findByUser($user),
            ];
        }
        $standaloneView->assignMultiple($variables);

        $response = ObjectUtility::getJsonResponse();
        $response->getBody()->write(json_encode(
            ['html' => $standaloneView->render()]
        ));
        return $response;
    }

    protected function addDocumentHeaderForNewsletterController(): void
    {
        $menuConfiguration = [
            'dashboard' => LocalizationUtility::translate('layout.backend.link.actiondashboard'),
            'list' => LocalizationUtility::translate('layout.backend.link.actionnewsletter'),
        ];
        if (ConfigurationUtility::isReceiverActionActivated()) {
            $menuConfiguration['receiver'] = LocalizationUtility::translate('layout.backend.link.actionreceiver');
        }
        $this->addDocumentHeader($menuConfiguration);
    }
}

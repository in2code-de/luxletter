<?php
declare(strict_types=1);
namespace In2code\Luxletter\Controller;

use Doctrine\DBAL\DBALException;
use In2code\Luxletter\Domain\Factory\UserFactory;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Domain\Service\ParseNewsletterService;
use In2code\Luxletter\Domain\Service\ParseNewsletterUrlService;
use In2code\Luxletter\Domain\Service\QueueService;
use In2code\Luxletter\Mail\SendMail;
use In2code\Luxletter\Utility\BackendUserUtility;
use In2code\Luxletter\Utility\LocalizationUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;
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
     * @var NewsletterRepository
     */
    protected $newsletterRepository = null;

    /**
     * @param NewsletterRepository $newsletterRepository
     * @return void
     */
    public function injectNewsletterRepository(NewsletterRepository $newsletterRepository)
    {
        $this->newsletterRepository = $newsletterRepository;
    }

    /**
     * @return void
     */
    public function dashboardAction(): void
    {
    }

    /**
     * @return void
     */
    public function listAction(): void
    {
        $newsletters = $this->newsletterRepository->findAll();
        $this->view->assign('newsletters', $newsletters);
    }

    /**
     * @return void
     */
    public function newAction(): void
    {
    }

    /**
     * @return void
     * @throws InvalidArgumentNameException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
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
     * @throws IllegalObjectTypeException
     * @throws StopActionException
     * @throws UnsupportedRequestTypeException
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
     * @throws UnsupportedRequestTypeException
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
     * @throws UnsupportedRequestTypeException
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
     * @throws UnsupportedRequestTypeException
     */
    public function deleteAction(Newsletter $newsletter)
    {
        $this->newsletterRepository->remove($newsletter);
        $this->addFlashMessage(LocalizationUtility::translate('module.newsletter.delete.message'));
        $this->redirect('list');
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws DBALException
     */
    public function wizardUserPreviewAjax(
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface
    {
        $userRepository = ObjectUtility::getObjectManager()->get(UserRepository::class);
        $standaloneView = ObjectUtility::getObjectManager()->get(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->wizardUserPreviewFile));
        $standaloneView->assignMultiple([
            'userPreview' => $userRepository->getUsersFromGroup((int)$request->getQueryParams()['usergroup'], 3),
            'userAmount' => $userRepository->getUserAmountFromGroup((int)$request->getQueryParams()['usergroup'])
        ]);
        $response->getBody()->write(json_encode(
            ['html' => $standaloneView->render()]
        ));
        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function testMailAjax(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if (BackendUserUtility::isBackendUserAuthenticated() === false) {
            throw new \LogicException('You are not authenticated to send mails', 1560872725);
        }
        $parseUrlService = ObjectUtility::getObjectManager()->get(
            ParseNewsletterUrlService::class,
            $request->getQueryParams()['origin']
        );
        $parseService = ObjectUtility::getObjectManager()->get(ParseNewsletterService::class);
        $userFactory = ObjectUtility::getObjectManager()->get(UserFactory::class);
        $mailService = ObjectUtility::getObjectManager()->get(
            SendMail::class,
            $parseService->parseBodytext($request->getQueryParams()['subject'], $userFactory->getDummyUser()),
            $parseUrlService->getParsedContent()
        );
        $status = $mailService->sendNewsletter($request->getQueryParams()['email']) > 0;
        $response->getBody()->write(json_encode(['status' => $status]));
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
     * @throws InvalidArgumentNameException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws NoSuchArgumentException
     */
    protected function parseNewsletterToBodytext(): void
    {
        $newsletter = (array)$this->request->getArgument('newsletter');
        $parseService = ObjectUtility::getObjectManager()->get(ParseNewsletterUrlService::class, $newsletter['origin']);
        $parseService->setParseVariables(false);
        $newsletter['bodytext'] = $parseService->getParsedContent();
        $this->request->setArgument('newsletter', $newsletter);
    }
}
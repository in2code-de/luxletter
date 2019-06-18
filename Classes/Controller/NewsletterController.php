<?php
declare(strict_types=1);
namespace In2code\Luxletter\Controller;

use Doctrine\DBAL\DBALException;
use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Mail\SendMail;
use In2code\Luxletter\Utility\BackendUserUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
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
    }

    /**
     * @return void
     */
    public function newAction(): void
    {
    }

    /**
     * @return void
     */
    public function createAction(): void
    {
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws DBALException
     */
    public function wizardUserPreviewAjax(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $userRepository = ObjectUtility::getObjectManager()->get(UserRepository::class);
        $standaloneView = ObjectUtility::getObjectManager()->get(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->wizardUserPreviewFile));
        $standaloneView->assignMultiple([
            'userPreview' => $userRepository->getUserPreviewFromGroup((int)$request->getQueryParams()['usergroup']),
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
        $mailService = ObjectUtility::getObjectManager()->get(
            SendMail::class,
            $request->getQueryParams()['subject'],
            $request->getQueryParams()['origin']
        );
        $status = $mailService->sendNewsletter($request->getQueryParams()['email']) > 0;
        $response->getBody()->write(json_encode(['status' => $status]));
        return $response;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Luxletter\Controller;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Exception;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\UsergroupRepository;
use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Domain\Service\LogService;
use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;
use In2code\Luxletter\Domain\Service\SiteService;
use In2code\Luxletter\Exception\ArgumentMissingException;
use In2code\Luxletter\Exception\AuthenticationFailedException;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Exception\MissingRelationException;
use In2code\Luxletter\Exception\UserValuesAreMissingException;
use In2code\Luxletter\Utility\BackendUserUtility;
use In2code\Luxletter\Utility\LocalizationUtility;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

class FrontendController extends ActionController
{
    protected ?UserRepository $userRepository;
    protected UsergroupRepository $usergroupRepository;
    protected LogService $logService;
    protected ?ModuleTemplateFactory $moduleTemplateFactory = null;
    protected ?ModuleTemplate $moduleTemplate = null;

    public function __construct(
        UserRepository $userRepository,
        UsergroupRepository $usergroupRepository,
        LogService $logService,
        ModuleTemplateFactory $moduleTemplateFactory
    ) {
        $this->userRepository = $userRepository;
        $this->usergroupRepository = $usergroupRepository;
        $this->logService = $logService;
        $this->moduleTemplateFactory = $moduleTemplateFactory;
    }

    public function initializePreviewAction(): void
    {
        if (BackendUserUtility::isBackendUserAuthenticated() === false) {
            throw new AuthenticationFailedException('You are not authenticated to see this view', 1560778826);
        }
    }

    public function previewAction(string $origin, string $layout, int $language = 0): ResponseInterface
    {
        try {
            $siteService = GeneralUtility::makeInstance(SiteService::class);
            $urlService = GeneralUtility::makeInstance(NewsletterUrl::class, $origin, $layout, $language)
                ->setModePreview();
            $content = $urlService->getParsedContent($siteService->getSite());
        } catch (Exception $exception) {
            $content = 'Error: Origin ' . htmlspecialchars($origin) . ' could not be converted into a valid url!<br>'
                . 'Reason: ' . $exception->getMessage() . ' (' . $exception->getCode() . ')';
        }

        return $this->htmlResponse($content);
    }

    /**
     * Render a transparent gif and track the access as email-opening
     *
     * @param Newsletter|null $newsletter
     * @param User|null $user
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws ExceptionDbalDriver
     * @throws DBALException
     */
    public function trackingPixelAction(Newsletter $newsletter = null, User $user = null): ResponseInterface
    {
        if ($newsletter !== null && $user !== null) {
            $this->logService->logNewsletterOpening($newsletter, $user);
        }
        $content = base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
        return $this->htmlResponse($content);
    }

    /**
     * Remove user from all to newsletter related usergroups
     *
     * @param User|null $user
     * @param Newsletter|null $newsletter
     * @param string $hash
     * @return ResponseInterface
     */
    public function unsubscribeAction(
        User $user = null,
        Newsletter $newsletter = null,
        string $hash = ''
    ): ResponseInterface {
        try {
            $this->checkArgumentsForUnsubscribe($user, $newsletter, $hash);
            foreach ($newsletter->getReceivers() as $group) {
                $user->removeUsergroup($group);
            }
            $this->userRepository->update($user);
            $this->userRepository->persistAll();
            $this->view->assignMultiple([
                'success' => true,
                'user' => $user,
                'hash' => $hash,
                'usergroupToRemove' => $newsletter->getReceivers(),
            ]);
            $this->logService->logUnsubscribe($newsletter, $user);
        } catch (Throwable $exception) {
            $languageKey = 'fe.unsubscribe.message.' . $exception->getCode();
            $message = LocalizationUtility::translate($languageKey);
            $this->addFlashMessage(($languageKey !== $message) ? $message : $exception->getMessage());
        }
        return $this->htmlResponse();
    }

    /**
     * @param User|null $user
     * @param Newsletter|null $newsletter
     * @param string $hash
     * @return void
     * @throws ArgumentMissingException
     * @throws AuthenticationFailedException
     * @throws MissingRelationException
     * @throws UserValuesAreMissingException
     * @throws MisconfigurationException
     */
    protected function checkArgumentsForUnsubscribe(
        User $user = null,
        Newsletter $newsletter = null,
        string $hash = ''
    ): void {
        if ($user === null) {
            throw new ArgumentMissingException('User not given', 1562050511);
        }
        if ($newsletter === null) {
            throw new ArgumentMissingException('Newsletter not given', 1562267031);
        }
        if ($hash === '') {
            throw new ArgumentMissingException('Hash not given', 1562050533);
        }
        $this->checkForUsergroups($user, $newsletter);
        if ($user->getUnsubscribeHash() !== $hash) {
            throw new AuthenticationFailedException('Given hash is incorrect', 1562069583);
        }
    }

    /**
     * @param User|null $user
     * @param Newsletter|null $newsletter
     * @return void
     * @throws MissingRelationException
     */
    protected function checkForUsergroups(User $user = null, Newsletter $newsletter = null): void
    {
        $match = false;
        foreach ($newsletter->getReceivers() as $group) {
            if ($user->getUsergroup()->contains($group) === true) {
                $match = true;
            }
        }
        if ($match === false) {
            throw new MissingRelationException('Usergroups not assigned to user', 1562066292);
        }
    }
}

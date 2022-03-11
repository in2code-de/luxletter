<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Controller;

use Exception;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Model\Usergroup;
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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Object\Exception as ExceptionExtbaseObject;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * Class FrontendController
 */
class FrontendController extends ActionController
{
    /**
     * @var UserRepository
     */
    protected $userRepository = null;

    /**
     * @var UsergroupRepository
     */
    protected $usergroupRepository = null;

    /**
     * @var LogService
     */
    protected $logService = null;

    /**
     * Constructor
     *
     * @param UserRepository $userRepository
     * @param UsergroupRepository $usergroupRepository
     * @param LogService $logService
     */
    public function __construct(
        UserRepository $userRepository,
        UsergroupRepository $usergroupRepository,
        LogService $logService
    ) {
        $this->userRepository = $userRepository;
        $this->usergroupRepository = $usergroupRepository;
        $this->logService = $logService;
    }

    /**
     * @return void
     * @throws AuthenticationFailedException
     */
    public function initializePreviewAction(): void
    {
        if (BackendUserUtility::isBackendUserAuthenticated() === false) {
            throw new AuthenticationFailedException('You are not authenticated to see this view', 1560778826);
        }
    }

    /**
     * @param string $origin URL or page identifier
     * @param string $layout Container HTML template filename
     * @param int $language
     * @return string
     */
    public function previewAction(string $origin, string $layout, int $language = 0): string
    {
        try {
            $siteService = GeneralUtility::makeInstance(SiteService::class);
            $urlService = GeneralUtility::makeInstance(NewsletterUrl::class, $origin, $layout, $language)
                ->setModePreview();
            return $urlService->getParsedContent($siteService->getSite());
        } catch (Exception $exception) {
            return 'Error: Origin ' . htmlspecialchars($origin) . ' could not be converted into a valid url!<br>'
                . 'Reason: ' . $exception->getMessage() . ' (' . $exception->getCode() . ')';
        }
    }

    /**
     * Render a transparent gif and track the access as email-opening
     *
     * @param Newsletter|null $newsletter
     * @param User|null $user
     * @return string
     * @throws IllegalObjectTypeException
     * @throws ExceptionExtbaseObject
     */
    public function trackingPixelAction(Newsletter $newsletter = null, User $user = null): string
    {
        if ($newsletter !== null && $user !== null) {
            $this->logService->logNewsletterOpening($newsletter, $user);
        }
        return base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
    }

    /**
     * @param User|null $user
     * @param Newsletter|null $newsletter
     * @param string $hash
     * @return void
     */
    public function unsubscribeAction(User $user = null, Newsletter $newsletter = null, string $hash = ''): void
    {
        try {
            $this->checkArgumentsForUnsubscribeAction($user, $newsletter, $hash);
            /** @var Usergroup $usergroupToRemove */
            $usergroupToRemove = $this->usergroupRepository->findByUid((int)$this->settings['removeusergroup']);
            $user->removeUsergroup($usergroupToRemove);
            $this->userRepository->update($user);
            $this->userRepository->persistAll();
            $this->view->assignMultiple([
                'success' => true,
                'user' => $user,
                'hash' => $hash,
                'usergroupToRemove' => $usergroupToRemove
            ]);
            if ($newsletter !== null) {
                $this->logService->logUnsubscribe($newsletter, $user);
            }
        } catch (Exception $exception) {
            $languageKey = 'fe.unsubscribe.message.' . $exception->getCode();
            $message = LocalizationUtility::translate($languageKey);
            $this->addFlashMessage(($languageKey !== $message) ? $message : $exception->getMessage());
        }
    }

    /**
     * @param User|null $user
     * @param Newsletter|null $newsletter
     * @param string $hash
     * @return void
     * @throws ArgumentMissingException
     * @throws AuthenticationFailedException
     * @throws ExceptionExtbaseObject
     * @throws MissingRelationException
     * @throws UserValuesAreMissingException
     * @throws MisconfigurationException
     */
    protected function checkArgumentsForUnsubscribeAction(
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
        /** @var Usergroup $usergroupToRemove */
        $usergroupToRemove = $this->usergroupRepository->findByUid((int)$this->settings['removeusergroup']);
        if ($user->getUsergroup()->contains($usergroupToRemove) === false) {
            throw new MissingRelationException('Usergroup not assigned to user', 1562066292);
        }
        if ($user->getUnsubscribeHash() !== $hash) {
            throw new AuthenticationFailedException('Given hash is incorrect', 1562069583);
        }
    }
}

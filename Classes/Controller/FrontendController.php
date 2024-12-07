<?php

declare(strict_types=1);
namespace In2code\Luxletter\Controller;

use Doctrine\DBAL\Exception as ExceptionDbal;
use Exception;
use In2code\Luxletter\Domain\Factory\UsergroupFactory;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\ContentRepository;
use In2code\Luxletter\Domain\Repository\UsergroupRepository;
use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Domain\Service\LogService;
use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;
use In2code\Luxletter\Domain\Service\SiteService;
use In2code\Luxletter\Exception\ArgumentMissingException;
use In2code\Luxletter\Exception\AuthenticationFailedException;
use In2code\Luxletter\Exception\DisallowedArgumentException;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Exception\MissingRelationException;
use In2code\Luxletter\Exception\UserValuesAreMissingException;
use In2code\Luxletter\Utility\ArrayUtility;
use In2code\Luxletter\Utility\BackendUserUtility;
use In2code\Luxletter\Utility\LocalizationUtility;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class FrontendController extends ActionController
{
    public function __construct(
        readonly protected UserRepository $userRepository,
        readonly protected UsergroupRepository $usergroupRepository,
        readonly protected ContentRepository $contentRepository,
        readonly protected LogService $logService,
        readonly protected UsergroupFactory $usergroupFactory
    ) {
    }

    /**
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function initializeView()
    {
        $this->view->assignMultiple([
            'data' => $this->request->getAttribute('currentContentObject')->data,
            'extensionConfiguration' => GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('luxletter'),
        ]);
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
     * @throws ExceptionDbal
     */
    public function trackingPixelAction(Newsletter $newsletter = null, User $user = null): ResponseInterface
    {
        if ($newsletter !== null && $user !== null) {
            $this->logService->logNewsletterOpening($newsletter->getUid(), $user->getUid());
        }
        $content = base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');
        return $this->htmlResponse($content);
    }

    /**
     * Remove user automatically from all usergroups that are related to given newsletter
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
            $this->checkForUsergroups($user, $newsletter);
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
     * Use same arguments as from default unsubscribe plugin
     *
     * @return void
     * @throws ArgumentMissingException
     * @throws AuthenticationFailedException
     * @throws MisconfigurationException
     * @throws UserValuesAreMissingException
     */
    public function initializeUnsubscribe2Action(): void
    {
        $arguments = $_REQUEST['tx_luxletter_fe'] ?? [];
        if (is_array($arguments)) {
            $this->request = $this->request->withArguments($arguments);
        }
    }

    public function unsubscribe2Action(
        User $user = null,
        Newsletter $newsletter = null,
        string $hash = ''
    ): ResponseInterface {
        try {
            $this->checkArgumentsForUnsubscribe($user, $newsletter, $hash);
            $usergroups = $this->usergroupRepository->findByIdentifiersAndKeepOrderings(
                GeneralUtility::intExplode(',', $this->settings['usergroups'] ?? '', true)
            );
            $this->view->assignMultiple([
                'success' => true,
                'usergroups' => $usergroups,
                'user' => $user,
                'newsletter' => $newsletter,
                'hash' => $hash,
            ]);
            $this->logService->logUnsubscribe($newsletter, $user);
        } catch (Throwable $exception) {
            $languageKey = 'fe.unsubscribe.message.' . $exception->getCode();
            $message = LocalizationUtility::translate($languageKey);
            if ($languageKey === $message) {
                $message = 'Unknown error (' . $exception->getCode() . ')';
            }
            $this->addFlashMessage($message);
        }
        return $this->htmlResponse();
    }

    /**
     * @param User|null $user
     * @param Newsletter|null $newsletter
     * @param string $hash
     * @param array $usergroups
     * @param int $contentIdentifier
     * @return ResponseInterface
     */
    public function unsubscribe2UpdateAction(
        User $user = null,
        Newsletter $newsletter = null,
        string $hash = '',
        array $usergroups = [],
        int $contentIdentifier = 0
    ): ResponseInterface {
        try {
            $usergroups = ArrayUtility::convertToIntegerArray($usergroups);
            if ($contentIdentifier < 1) {
                throw new ArgumentMissingException('Invalid content identifier', 1562050511);
            }
            $allowedGroups = $this->contentRepository->findConfiguredUsergroupIdentifiersByContentIdentifier(
                $contentIdentifier
            );
            $this->checkArgumentsForUnsubscribe($user, $newsletter, $hash);
            $this->checkForAllowedUsergroups($usergroups, $allowedGroups);

            $this->usergroupFactory->updateUsergroupsInUser($user, $usergroups, $allowedGroups);
            $this->userRepository->update($user);
            $this->userRepository->persistAll();
            $this->addFlashMessage(LocalizationUtility::translate('fe.unsubscribe2.message.success'));
        } catch (Throwable $exception) {
            $languageKey = 'fe.unsubscribe.message.' . $exception->getCode();
            $message = LocalizationUtility::translate($languageKey);
            if ($languageKey === $message) {
                $message = 'Unknown error (' . $exception->getCode() . ')';
            }
            $this->addFlashMessage($message);
        }
        return $this->redirect(
            'unsubscribe2',
            null,
            null,
            ['user' => $user, 'newsletter' => $newsletter, 'hash' => $hash]
        );
    }

    /**
     * @param User|null $user
     * @param Newsletter|null $newsletter
     * @param string $hash
     * @return void
     * @throws ArgumentMissingException
     * @throws AuthenticationFailedException
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

    /**
     * @param array $givenUsergroups
     * @param array $allowedGroups
     * @return void
     * @throws DisallowedArgumentException
     */
    protected function checkForAllowedUsergroups(array $givenUsergroups, array $allowedGroups): void
    {
        foreach ($givenUsergroups as $givenUsergroup) {
            if (in_array($givenUsergroup, $allowedGroups) === false) {
                throw new DisallowedArgumentException('Usergroup is not allowed', 1693909396);
            }
        }
    }
}

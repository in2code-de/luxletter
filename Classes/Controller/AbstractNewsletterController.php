<?php

declare(strict_types=1);
namespace In2code\Luxletter\Controller;

use DateTime;
use Exception;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Repository\CategoryRepository;
use In2code\Luxletter\Domain\Repository\ConfigurationRepository;
use In2code\Luxletter\Domain\Repository\LogRepository;
use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Domain\Repository\PageRepository;
use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Domain\Service\LayoutService;
use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;
use In2code\Luxletter\Exception\ApiConnectionException;
use In2code\Luxletter\Exception\InvalidUrlException;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\BackendUserUtility;
use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentNameException;
use TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

/**
 * Class AbstractNewsletterController
 */
abstract class AbstractNewsletterController extends ActionController
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
     * Pass some important variables to all views
     *
     * @param ViewInterface $view
     * @return void
     */
    public function initializeView(ViewInterface $view)
    {
        parent::initializeView($view);
        $this->view->assignMultiple([
            'view' => [
                'controller' => $this->getControllerName(),
                'action' => $this->getActionName(),
            ],
        ]);
    }

    /**
     * @return void
     * @throws InvalidArgumentNameException
     * @throws NoSuchArgumentException
     */
    protected function setFilter(): void
    {
        $filterArgument = $this->arguments->getArgument('filter');
        $filterPropertyMapping = $filterArgument->getPropertyMappingConfiguration();
        $filterPropertyMapping->allowAllProperties();
        if ($this->request->hasArgument('filter') === false) {
            $filter = BackendUserUtility::getSessionValue('filter', $this->getActionName(), $this->getControllerName());
        } else {
            $filter = (array)$this->request->getArgument('filter');
            BackendUserUtility::saveValueToSession(
                'filter',
                $this->getActionName(),
                $this->getControllerName(),
                $filter
            );
        }
        if (isset($filter['usergroup']['__identity']) && $filter['usergroup']['__identity'] === '0') {
            unset($filter['usergroup']);
        }
        if (array_key_exists('category', $filter) && (is_array($filter['category']) || $filter['category'] === '')) {
            $filter['category'] = 0;
        }
        $this->request->setArgument('filter', $filter);
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

    /**
     * @return string like "Analysis" or "Lead"
     */
    protected function getControllerName(): string
    {
        $classParts = explode('\\', static::class);
        $name = end($classParts);
        return StringUtility::removeStringPostfix($name, 'Controller');
    }

    /**
     * @return string like "list" or "detail"
     */
    protected function getActionName(): string
    {
        return StringUtility::removeStringPostfix($this->actionMethodName, 'Action');
    }
}

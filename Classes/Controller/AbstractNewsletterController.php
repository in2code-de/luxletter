<?php

declare(strict_types=1);
namespace In2code\Luxletter\Controller;

use DateTime;
use In2code\Luxletter\Backend\Buttons\NavigationGroupButton;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Repository\CategoryRepository;
use In2code\Luxletter\Domain\Repository\ConfigurationRepository;
use In2code\Luxletter\Domain\Repository\LogRepository;
use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Domain\Repository\PageRepository;
use In2code\Luxletter\Domain\Repository\UsergroupRepository;
use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Domain\Service\LayoutService;
use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;
use In2code\Luxletter\Utility\BackendUserUtility;
use In2code\Luxletter\Utility\StringUtility;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

abstract class AbstractNewsletterController extends ActionController
{
    protected string $wizardUserPreviewFile =
        'EXT:luxletter/Resources/Private/Templates/Newsletter/WizardUserPreview.html';
    protected string $receiverDetailFile = 'EXT:luxletter/Resources/Private/Templates/Newsletter/ReceiverDetail.html';

    protected ModuleTemplateFactory $moduleTemplateFactory;
    protected IconFactory $iconFactory;
    protected ModuleTemplate $moduleTemplate;
    protected NewsletterRepository $newsletterRepository;
    protected UserRepository $userRepository;
    protected UsergroupRepository $usergroupRepository;
    protected LogRepository $logRepository;
    protected ConfigurationRepository $configurationRepository;
    protected PageRepository $pageRepository;
    protected LayoutService $layoutService;
    protected CategoryRepository $categoryRepository;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        IconFactory $iconFactory,
        NewsletterRepository $newsletterRepository,
        UserRepository $userRepository,
        UsergroupRepository $usergroupRepository,
        LogRepository $logRepository,
        ConfigurationRepository $configurationRepository,
        PageRepository $pageRepository,
        LayoutService $layoutService,
        CategoryRepository $categoryRepository
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->iconFactory = $iconFactory;
        $this->newsletterRepository = $newsletterRepository;
        $this->userRepository = $userRepository;
        $this->usergroupRepository = $usergroupRepository;
        $this->logRepository = $logRepository;
        $this->configurationRepository = $configurationRepository;
        $this->pageRepository = $pageRepository;
        $this->layoutService = $layoutService;
        $this->categoryRepository = $categoryRepository;
    }

    public function initializeView($view)
    {
        $this->view->assignMultiple([
            'view' => [
                'controller' => $this->getControllerName(),
                'action' => $this->getActionName(),
            ],
        ]);
    }

    public function initializeAction()
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
    }

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
        $clearFields = ['usergroup', 'configuration', 'category'];
        foreach ($clearFields as $clearField) {
            if (($filter[$clearField] ?? '0') === '0' || is_array($filter[$clearField])) {
                unset($filter[$clearField]);
            }
        }
        $this->request = $this->request->withArgument('filter', $filter);
    }

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

            $this->request = $this->request->withArgument('newsletter', $newsletter);
        }
    }

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

    protected function defaultRendering(): ResponseInterface
    {
        $this->moduleTemplate->setContent($this->view->render());
        return $this->htmlResponse($this->moduleTemplate->renderContent());
    }

    protected function addDocumentHeader(array $configuration): void
    {
        $this->addNavigationButtons($configuration);
        $this->addShortcutButton();
    }

    protected function addNavigationButtons(array $configuration): void
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $navigationGroupButton = GeneralUtility::makeInstance(
            NavigationGroupButton::class,
            $this->request,
            $this->getActionName(),
            $configuration,
        );
        $buttonBar->addButton($navigationGroupButton, ButtonBar::BUTTON_POSITION_LEFT, 2);
    }

    protected function addShortcutButton(): void
    {
        $buttonBar = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar();
        $shortCutButton = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar()->makeShortcutButton();
        $shortCutButton
            ->setRouteIdentifier('lux_Luxletter')
            ->setDisplayName('Shortcut')
            ->setArguments(['action' => $this->getActionName(), 'controller' => $this->getControllerName()]);
        $buttonBar->addButton($shortCutButton, ButtonBar::BUTTON_POSITION_RIGHT, 1);
    }
}

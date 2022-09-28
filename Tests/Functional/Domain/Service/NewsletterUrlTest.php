<?php

namespace In2code\Luxletter\Tests\Functional\Domain\Service;

use In2code\Luxletter\Domain\Factory\UserFactory;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Service\Parsing\Newsletter;
use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class NewsletterUrlTest
 * @coversDefaultClass \In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl
 */
class NewsletterUrlTest extends FunctionalTestCase
{
    /**
     * @var NewsletterUrl
     */
    protected $subject;

    /**
     * @var ObjectManagerInterface|ObjectProphecy
     */
    protected $objectManager;

    /**
     * @var ConfigurationManagerInterface|ObjectProphecy
     */
    protected $configurationManager;

    /**
     * @var UserFactory|ObjectProphecy
     */
    protected $userFactory;

    /**
     * @var Newsletter|ObjectProphecy
     */
    protected $parseNewsletterService;

    /**
     * @var StandaloneView|ObjectProphecy
     */
    protected $standaloneView;

    protected function setUp()
    {
        $this->subject = GeneralUtility::makeInstance(NewsletterUrl::class, 'http://example.com/');

        /** @var Dispatcher|ObjectProphecy $dispatcher */
        $dispatcher = $this->prophesize(Dispatcher::class);
        $dispatcher
            ->dispatch(Argument::cetera())
            ->willReturn([]);

        $user = new User();
        $user->setEmail('dummy@example.com');
        $user->setFirstName('Max');
        $user->setLastName('Mustermann');

        $this->userFactory = $this->prophesize(UserFactory::class);
        $this->userFactory
            ->getDummyUser()
            ->shouldBeCalled()
            ->willReturn($user);

        $this->parseNewsletterService = $this->prophesize(Newsletter::class);
        $this->parseNewsletterService
            ->parseSubject(Argument::cetera())
            ->shouldBeCalled()
            ->willReturn('<div>Hello</div>');

        $this->configurationManager = $this->prophesize(ConfigurationManager::class);

        $this->standaloneView = $this->prophesize(StandaloneView::class);
        $this->standaloneView
            ->setTemplateRootPaths([])
            ->shouldBeCalled();
        $this->standaloneView
            ->setLayoutRootPaths([])
            ->shouldBeCalled();
        $this->standaloneView
            ->setPartialRootPaths([])
            ->shouldBeCalled();
        $this->standaloneView
            ->setTemplate('Mail/NewsletterContainer.html')
            ->shouldBeCalled();
        $this->standaloneView
            ->render()
            ->shouldBeCalled()
            ->willReturn('');

        /** @var TypoScriptFrontendController|ObjectProphecy $tsfe */
        $tsfeProphecy = $this->prophesize(TypoScriptFrontendController::class);

        $this->objectManager = $this->prophesize(ObjectManager::class);
        $this->objectManager
            ->get(Dispatcher::class)
            ->shouldBeCalled()
            ->willReturn($dispatcher->reveal());
        $this->objectManager
            ->get(UserFactory::class)
            ->shouldBeCalled()
            ->willReturn($this->userFactory->reveal());
        $this->objectManager
            ->get(Newsletter::class)
            ->shouldBeCalled()
            ->willReturn($this->parseNewsletterService->reveal());
        $this->objectManager
            ->get(ConfigurationManager::class)
            ->shouldBeCalled()
            ->willReturn($this->configurationManager->reveal());
        $this->objectManager
            ->get(StandaloneView::class)
            ->shouldBeCalled()
            ->willReturn($this->standaloneView->reveal());
        $this->objectManager
            ->get(TypoScriptService::class)
            ->shouldBeCalled()
            ->willReturn(new TypoScriptService());
        $this->objectManager
            ->get(ContentObjectRenderer::class)
            ->shouldBeCalled()
            ->willReturn(new ContentObjectRenderer($tsfeProphecy->reveal()));
        GeneralUtility::setSingletonInstance(ObjectManager::class, $this->objectManager->reveal());
    }

    protected function tearDown()
    {
        unset(
            $this->subject,
            $this->objectManager,
            $this->userFactory,
            $this->parseNewsletterService,
            $this->configurationManager,
            $this->standaloneView
        );
    }

    /**
     * @test
     */
    public function getParsedContentWithNoUserWillAddFluidSettings()
    {
        $this->configurationManager
            ->getConfiguration('Framework', 'luxletter')
            ->shouldBeCalled()
            ->willReturn([
                'view' => [
                    'templateRootPaths' => [],
                    'layoutRootPaths' => [],
                    'partialRootPaths' => [],
                ],
                'settings' => [
                    'foo' => 'bar',
                ],
            ]);

        $this->standaloneView
            ->assignMultiple(Argument::cetera())
            ->shouldBeCalled();
        $this->standaloneView
            ->assign('settings', ['foo' => 'bar'])
            ->shouldBeCalled();

        $this->subject->getParsedContent();
    }

    /**
     * @test
     */
    public function getParsedContentWithNoUserWillAddFluidVariables()
    {
        $this->configurationManager
            ->getConfiguration('Framework', 'luxletter')
            ->shouldBeCalled()
            ->willReturn([
                'view' => [
                    'templateRootPaths' => [],
                    'layoutRootPaths' => [],
                    'partialRootPaths' => [],
                ],
                'variables' => [
                    'subject' => [
                        '_typoScriptNodeValue' => 'TEXT',
                        'value' => 'Hello world',
                    ],
                ],
            ]);

        $this->standaloneView
            ->assignMultiple([
                'subject' => 'Hello world',
            ])
            ->shouldBeCalled();
        $this->standaloneView
            ->assignMultiple(Argument::cetera())
            ->shouldBeCalled();
        $this->standaloneView
            ->assign('settings', [])
            ->shouldBeCalled();

        $this->subject->getParsedContent();
    }
}

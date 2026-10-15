<?php

namespace In2code\Luxletter\Tests\Unit\Domain\Service;

use Exception;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Service\SiteService;
use In2code\Luxletter\Domain\Service\UnsubscribeUrlService;
use In2code\Luxletter\Events\UnsubscribeUrlEvent;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Tests\Unit\Fixtures\DomainObjectTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\EventDispatcher\NoopEventDispatcher;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(UnsubscribeUrlService::class)]
class UnsubscribeUrlServiceTest extends UnitTestCase
{
    use DomainObjectTrait;

    protected SiteService&MockObject $siteService;
    protected EventDispatcherInterface $eventDispatcher;

    public function setUp(): void
    {
        parent::setUp();
        $this->siteService = $this->createMock(SiteService::class);
        $this->eventDispatcher = new NoopEventDispatcher();
    }

    /**
     * Preview and test mails have no newsletter and no user
     *
     * @return void
     */
    public function testGetReturnsPlaceholderWithoutNewsletterAndUser(): void
    {
        $this->siteService->expects(self::never())->method('getPageUrlFromParameter');
        $subject = $this->getSubject(null, null, $this->getSite(12), 0);

        self::assertSame('#', $subject->get());
    }

    /**
     * @return void
     */
    public function testGetBuildsUrlToUnsubscribePageWithUserNewsletterAndHash(): void
    {
        $this->siteService
            ->expects(self::once())
            ->method('getPageUrlFromParameter')
            ->with(
                12,
                [
                    'foo' => 'bar',
                    'tx_luxletter_fe' => [
                        'user' => 5,
                        'newsletter' => 3,
                        'hash' => 'unsubscribehash',
                    ],
                    '_language' => 1,
                ]
            )
            ->willReturn('https://www.in2code.de/unsubscribe');
        $subject = $this->getSubject(
            $this->getNewsletter(3),
            $this->getUserStub(5),
            $this->getSite(12),
            1,
            ['foo' => 'bar']
        );

        self::assertSame('https://www.in2code.de/unsubscribe', $subject->get());
    }

    /**
     * @return void
     */
    public function testGetReturnsUrlFromEvent(): void
    {
        $this->eventDispatcher = self::createStub(EventDispatcherInterface::class);
        $this->eventDispatcher->method('dispatch')->willReturnCallback(
            static fn (UnsubscribeUrlEvent $event): UnsubscribeUrlEvent => $event->setUrl('https://changed.org')
        );
        $subject = $this->getSubject(null, null, $this->getSite(12), 0);

        self::assertSame('https://changed.org', $subject->get());
    }

    /**
     * @return void
     */
    public function testGetThrowsExceptionWithoutUnsubscribePidInSiteConfiguration(): void
    {
        $subject = $this->getSubject(
            $this->getNewsletter(3),
            $this->getUserStub(5),
            $this->getSite(0),
            0
        );

        $this->expectException(MisconfigurationException::class);
        $this->expectExceptionCode(1682077001);
        $subject->get();
    }

    /**
     * @return void
     */
    public function testGetConvertsUrlBuildingErrorsIntoMisconfigurationException(): void
    {
        $this->siteService->method('getPageUrlFromParameter')->willThrowException(new Exception('error', 123));
        $subject = $this->getSubject(
            $this->getNewsletter(3),
            $this->getUserStub(5),
            $this->getSite(12),
            0
        );

        $this->expectException(MisconfigurationException::class);
        $this->expectExceptionCode(1646380245);
        $subject->get();
    }

    protected function getSubject(
        ?Newsletter $newsletter,
        ?User $user,
        Site $site,
        int $language,
        array $additionalArguments = []
    ): UnsubscribeUrlService {
        GeneralUtility::addInstance(SiteService::class, $this->siteService);
        GeneralUtility::addInstance(EventDispatcherInterface::class, $this->eventDispatcher);
        return new UnsubscribeUrlService($newsletter, $user, $site, $language, $additionalArguments);
    }

    protected function getSite(int $unsubscribePid): Site
    {
        $configuration = [];
        if ($unsubscribePid > 0) {
            $configuration['luxletterUnsubscribePid'] = $unsubscribePid;
        }
        $site = self::createStub(Site::class);
        $site->method('getConfiguration')->willReturn($configuration);
        $site->method('getIdentifier')->willReturn('main');
        return $site;
    }

    protected function getUserStub(int $identifier): User
    {
        $user = self::createStub(User::class);
        $user->method('getUid')->willReturn($identifier);
        $user->method('getUnsubscribeHash')->willReturn('unsubscribehash');
        return $user;
    }
}

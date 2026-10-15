<?php

namespace In2code\Luxletter\Tests\Unit\Domain\Service\BodytextManipulation;

use In2code\Luxletter\Domain\Model\Configuration;
use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\LinkRepository;
use In2code\Luxletter\Domain\Service\BodytextManipulation\LinkHashing;
use In2code\Luxletter\Domain\Service\SiteService;
use In2code\Luxletter\Events\HashLinkEvent;
use In2code\Luxletter\Events\HashLinksEvent;
use In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\BodytextManipulation\LinkHashingFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\EventDispatcher\NoopEventDispatcher;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(LinkHashing::class)]
class LinkHashingTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    protected LinkRepository&MockObject $linkRepository;
    protected EventDispatcherInterface $eventDispatcher;
    protected Newsletter $newsletter;
    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
        $configuration = self::createStub(Configuration::class);
        $configuration->method('getSiteConfiguration')->willReturn(self::createStub(Site::class));
        $this->newsletter = new Newsletter();
        $this->newsletter->setConfiguration($configuration);
        $this->user = new User();
        $this->linkRepository = $this->createMock(LinkRepository::class);
        $this->eventDispatcher = new NoopEventDispatcher();
    }

    /**
     * @return void
     */
    public function testConvertToAbsoluteHrefKeepsAbsoluteUrl(): void
    {
        $subject = $this->getAccessibleMock(LinkHashingFixture::class, null, [$this->newsletter, $this->user]);
        $testUri = 'https://test.org';
        self::assertSame($testUri, $subject->_call('convertToAbsoluteHref', $testUri));
    }

    /**
     * @return void
     */
    public function testHashLinksReplacesAbsoluteLinkWithTrackingLink(): void
    {
        $link = $this->addLink('https://www.in2code.de/target', 'https://www.in2code.de/?luxletterlink=hash1');
        $this->linkRepository->expects(self::once())->method('add')->with($link);

        $content = $this->getSubject()->hashLinks('<p><a href="https://www.in2code.de/target">Link</a></p>');

        self::assertStringContainsString('href="https://www.in2code.de/?luxletterlink=hash1"', $content);
        self::assertStringNotContainsString('href="https://www.in2code.de/target"', $content);
    }

    /**
     * @return void
     */
    public function testHashLinksConvertsRelativeLinkToAbsoluteLinkBeforeHashing(): void
    {
        $this->addSiteService();
        $this->addLink('https://www.in2code.de/folder/page', 'https://www.in2code.de/?luxletterlink=hash1');
        $this->linkRepository->expects(self::once())->method('add');

        $content = $this->getSubject()->hashLinks('<p><a href="/folder/page">Link</a></p>');

        self::assertStringContainsString('href="https://www.in2code.de/?luxletterlink=hash1"', $content);
    }

    /**
     * @return void
     */
    public function testHashLinksHashesEveryLink(): void
    {
        $this->addLink('https://www.in2code.de/one', 'https://www.in2code.de/?luxletterlink=hash1');
        $this->addLink('https://www.in2code.de/two', 'https://www.in2code.de/?luxletterlink=hash2');
        $this->linkRepository->expects(self::exactly(2))->method('add');

        $content = $this->getSubject()->hashLinks(
            '<p><a href="https://www.in2code.de/one">One</a><a href="https://www.in2code.de/two">Two</a></p>'
        );

        self::assertStringContainsString('href="https://www.in2code.de/?luxletterlink=hash1"', $content);
        self::assertStringContainsString('href="https://www.in2code.de/?luxletterlink=hash2"', $content);
    }

    /**
     * @return void
     */
    public function testHashLinksKeepsLinkWithParselinkFalseAndRemovesTheAttribute(): void
    {
        $this->linkRepository->expects(self::never())->method('add');

        $content = $this->getSubject()->hashLinks(
            '<p><a href="https://www.in2code.de/target" data-luxletter-parselink="false">Link</a></p>'
        );

        self::assertStringContainsString('<a href="https://www.in2code.de/target">Link</a>', $content);
        self::assertStringNotContainsString('data-luxletter-parselink', $content);
    }

    /**
     * @return void
     */
    public function testHashLinksKeepsLinksWithoutValidUrl(): void
    {
        $this->linkRepository->expects(self::never())->method('add');

        $content = $this->getSubject()->hashLinks('<p><a href="#top">Anchor</a><a>No href</a></p>');

        self::assertStringContainsString('<a href="#top">Anchor</a>', $content);
        self::assertStringContainsString('<a>No href</a>', $content);
    }

    /**
     * @return void
     */
    public function testHashLinksDispatchesEventsForEachLinkAndForTheWholeContent(): void
    {
        $this->addLink('https://www.in2code.de/target', 'https://www.in2code.de/?luxletterlink=hash1');
        $dispatchedEvents = [];
        $this->eventDispatcher = self::createStub(EventDispatcherInterface::class);
        $this->eventDispatcher->method('dispatch')->willReturnCallback(
            static function (object $event) use (&$dispatchedEvents): object {
                $dispatchedEvents[] = $event::class;
                return $event;
            }
        );

        $this->getSubject()->hashLinks('<p><a href="https://www.in2code.de/target">Link</a></p>');

        self::assertSame([HashLinkEvent::class, HashLinksEvent::class], $dispatchedEvents);
    }

    protected function getSubject(): LinkHashing
    {
        GeneralUtility::setSingletonInstance(LinkRepository::class, $this->linkRepository);
        GeneralUtility::addInstance(EventDispatcherInterface::class, $this->eventDispatcher);
        return new LinkHashing($this->newsletter, $this->user);
    }

    /**
     * Queue a link stub for the next GeneralUtility::makeInstance(Link::class) call in LinkHashing::hashLink()
     *
     * @param string $expectedTarget
     * @param string $trackingUri
     * @return Link
     */
    protected function addLink(string $expectedTarget, string $trackingUri): Link
    {
        $link = $this->createMock(Link::class);
        $link->method('setNewsletter')->with($this->newsletter)->willReturnSelf();
        $link->method('setUser')->with($this->user)->willReturnSelf();
        $link->expects(self::once())->method('setTarget')->with($expectedTarget)->willReturnSelf();
        $link->method('getUriFromHash')->willReturn($trackingUri);
        GeneralUtility::addInstance(Link::class, $link);
        return $link;
    }

    protected function addSiteService(): void
    {
        $siteService = self::createStub(SiteService::class);
        $siteService->method('getDomainFromSite')->willReturn('https://www.in2code.de/');
        GeneralUtility::addInstance(SiteService::class, $siteService);
    }
}

<?php

namespace In2code\Luxletter\Tests\Unit\Middleware;

use In2code\Luxletter\Domain\Repository\LinkRepository;
use In2code\Luxletter\Domain\Service\LogService;
use In2code\Luxletter\Events\LuxletterLinkGetHashEvent;
use In2code\Luxletter\Events\LuxletterLinkProcessEvent;
use In2code\Luxletter\Middleware\LuxletterLink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\EventDispatcher\NoopEventDispatcher;
use TYPO3\CMS\Core\Http\Response;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(LuxletterLink::class)]
class LuxletterLinkTest extends UnitTestCase
{
    protected EventDispatcherInterface $eventDispatcher;
    protected LinkRepository&Stub $linkRepository;
    protected LogService&Stub $logService;
    protected RequestHandlerInterface&Stub $handler;
    protected ResponseInterface $handlerResponse;

    public function setUp(): void
    {
        parent::setUp();
        $this->eventDispatcher = new NoopEventDispatcher();
        $this->linkRepository = self::createStub(LinkRepository::class);
        $this->logService = self::createStub(LogService::class);
        $this->handlerResponse = new Response();
        $this->handler = self::createStub(RequestHandlerInterface::class);
        $this->handler->method('handle')->willReturn($this->handlerResponse);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function tearDown(): void
    {
        unset($_REQUEST['luxletterlink']);
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testProcessPassesRequestToNextHandlerWithoutLuxletterlinkParameter(): void
    {
        $this->linkRepository = $this->createMock(LinkRepository::class);
        $this->linkRepository->expects(self::never())->method('findOneByHashRaw');

        $response = $this->getSubject()->process(new ServerRequest('https://www.in2code.de/'), $this->handler);

        self::assertSame($this->handlerResponse, $response);
    }

    /**
     * @return void
     */
    public function testProcessPassesRequestToNextHandlerWithUnknownHash(): void
    {
        $this->linkRepository = $this->createMock(LinkRepository::class);
        $this->linkRepository->expects(self::once())->method('findOneByHashRaw')->with('unknown')->willReturn([]);
        $this->logService = $this->createMock(LogService::class);
        $this->logService->expects(self::never())->method('logLinkOpening');

        $response = $this->getSubject()->process($this->getRequest('unknown'), $this->handler);

        self::assertSame($this->handlerResponse, $response);
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testProcessIgnoresLuxletterlinkFromSuperglobals(): void
    {
        $_REQUEST['luxletterlink'] = 'hash1';
        $this->linkRepository = $this->createMock(LinkRepository::class);
        $this->linkRepository->expects(self::never())->method('findOneByHashRaw');

        $response = $this->getSubject()->process(new ServerRequest('https://www.in2code.de/'), $this->handler);

        self::assertSame($this->handlerResponse, $response);
    }

    /**
     * @return void
     */
    public function testProcessIgnoresLuxletterlinkThatIsNoString(): void
    {
        $this->linkRepository = $this->createMock(LinkRepository::class);
        $this->linkRepository->expects(self::never())->method('findOneByHashRaw');
        $request = (new ServerRequest('https://www.in2code.de/'))->withQueryParams(['luxletterlink' => ['hash1']]);

        $response = $this->getSubject()->process($request, $this->handler);

        self::assertSame($this->handlerResponse, $response);
    }

    /**
     * @return void
     */
    public function testProcessLogsClickAndRedirectsToTargetWithKnownHash(): void
    {
        $link = ['uid' => 1, 'user' => 5, 'newsletter' => 3, 'target' => 'https://www.in2code.de/target'];
        $this->linkRepository = $this->createMock(LinkRepository::class);
        $this->linkRepository->expects(self::once())->method('findOneByHashRaw')->with('hash1')->willReturn($link);
        $this->logService = $this->createMock(LogService::class);
        $this->logService->expects(self::once())->method('logLinkOpening')->with($link);
        $this->handler = $this->createMock(RequestHandlerInterface::class);
        $this->handler->expects(self::never())->method('handle');

        $response = $this->getSubject()->process($this->getRequest('hash1'), $this->handler);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('https://www.in2code.de/target', $response->getHeaderLine('location'));
    }

    /**
     * @return void
     */
    public function testProcessRedirectsToTargetChangedByEvent(): void
    {
        $this->linkRepository->method('findOneByHashRaw')->willReturn(['target' => 'https://www.in2code.de/target']);
        $this->eventDispatcher = self::createStub(EventDispatcherInterface::class);
        $this->eventDispatcher->method('dispatch')->willReturnCallback(
            static function (object $event): object {
                if ($event instanceof LuxletterLinkProcessEvent) {
                    $event->setLink(['target' => 'https://www.in2code.de/changed']);
                }
                return $event;
            }
        );

        $response = $this->getSubject()->process($this->getRequest('hash1'), $this->handler);

        self::assertSame('https://www.in2code.de/changed', $response->getHeaderLine('location'));
    }

    /**
     * @return void
     */
    public function testProcessUsesHashChangedByEventAndDispatchesItOnlyOnce(): void
    {
        $this->linkRepository = $this->createMock(LinkRepository::class);
        $this->linkRepository->expects(self::once())->method('findOneByHashRaw')->with('changed')->willReturn([]);
        $dispatchedHashEvents = 0;
        $this->eventDispatcher = self::createStub(EventDispatcherInterface::class);
        $this->eventDispatcher->method('dispatch')->willReturnCallback(
            static function (object $event) use (&$dispatchedHashEvents): object {
                if ($event instanceof LuxletterLinkGetHashEvent) {
                    $dispatchedHashEvents++;
                    $event->setHash('changed');
                }
                return $event;
            }
        );

        $this->getSubject()->process($this->getRequest('hash1'), $this->handler);

        self::assertSame(1, $dispatchedHashEvents);
    }

    protected function getRequest(string $hash): ServerRequest
    {
        return (new ServerRequest('https://www.in2code.de/'))->withQueryParams(['luxletterlink' => $hash]);
    }

    protected function getSubject(): LuxletterLink
    {
        return new LuxletterLink($this->eventDispatcher, $this->linkRepository, $this->logService);
    }
}

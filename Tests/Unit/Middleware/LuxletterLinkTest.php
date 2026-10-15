<?php

namespace In2code\Luxletter\Tests\Unit\Middleware;

use In2code\Luxletter\Domain\Repository\LinkRepository;
use In2code\Luxletter\Domain\Service\LogService;
use In2code\Luxletter\Events\LuxletterLinkProcessEvent;
use In2code\Luxletter\Middleware\LuxletterLink;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
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
    protected LinkRepository&MockObject $linkRepository;
    protected LogService&MockObject $logService;
    protected RequestHandlerInterface&MockObject $handler;
    protected ResponseInterface $handlerResponse;

    public function setUp(): void
    {
        parent::setUp();
        $this->eventDispatcher = new NoopEventDispatcher();
        $this->linkRepository = $this->createMock(LinkRepository::class);
        $this->logService = $this->createMock(LogService::class);
        $this->handlerResponse = new Response();
        $this->handler = $this->createMock(RequestHandlerInterface::class);
        $this->handler->method('handle')->willReturn($this->handlerResponse);
    }

    /**
     * @return void
     */
    public function testProcessPassesRequestToNextHandlerWithoutLuxletterlinkParameter(): void
    {
        $this->linkRepository->expects(self::never())->method('findOneByHashRaw');

        $response = $this->getSubject()->process(new ServerRequest('https://www.in2code.de/'), $this->handler);

        self::assertSame($this->handlerResponse, $response);
    }

    /**
     * @return void
     */
    public function testProcessPassesRequestToNextHandlerWithUnknownHash(): void
    {
        $_REQUEST['luxletterlink'] = 'unknown';
        $this->linkRepository->expects(self::once())->method('findOneByHashRaw')->with('unknown')->willReturn([]);
        $this->logService->expects(self::never())->method('logLinkOpening');

        $response = $this->getSubject()->process(new ServerRequest('https://www.in2code.de/'), $this->handler);

        self::assertSame($this->handlerResponse, $response);
    }

    /**
     * @return void
     */
    public function testProcessLogsClickAndRedirectsToTargetWithKnownHash(): void
    {
        $_REQUEST['luxletterlink'] = 'hash1';
        $link = ['uid' => 1, 'user' => 5, 'newsletter' => 3, 'target' => 'https://www.in2code.de/target'];
        $this->linkRepository->method('findOneByHashRaw')->with('hash1')->willReturn($link);
        $this->logService->expects(self::once())->method('logLinkOpening')->with($link);
        $this->handler->expects(self::never())->method('handle');

        $response = $this->getSubject()->process(new ServerRequest('https://www.in2code.de/'), $this->handler);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('https://www.in2code.de/target', $response->getHeaderLine('location'));
    }

    /**
     * @return void
     */
    public function testProcessRedirectsToTargetChangedByEvent(): void
    {
        $_REQUEST['luxletterlink'] = 'hash1';
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

        $response = $this->getSubject()->process(new ServerRequest('https://www.in2code.de/'), $this->handler);

        self::assertSame('https://www.in2code.de/changed', $response->getHeaderLine('location'));
    }

    protected function getSubject(): LuxletterLink
    {
        return new LuxletterLink($this->eventDispatcher, $this->linkRepository, $this->logService);
    }
}

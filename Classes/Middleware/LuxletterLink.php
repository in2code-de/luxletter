<?php

declare(strict_types=1);
namespace In2code\Luxletter\Middleware;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Luxletter\Domain\Repository\LinkRepository;
use In2code\Luxletter\Domain\Service\LogService;
use In2code\Luxletter\Events\LuxletterLinkGetHashEvent;
use In2code\Luxletter\Events\LuxletterLinkLuxIdentificationEvent;
use In2code\Luxletter\Events\LuxletterLinkProcessEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Package\Exception as ExceptionPackage;

/**
 * Class LuxletterLink
 * to redirect a luxletterlink to its target and track the click before
 */
class LuxletterLink implements MiddlewareInterface
{
    private const PARAMETER_NAME = 'luxletterlink';

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LinkRepository $linkRepository,
        private readonly LogService $logService
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ExceptionPackage
     * @throws ExceptionDbal
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $hashEvent = $this->getHashEvent($request);
        if ($hashEvent->isHashGiven()) {
            $link = $this->linkRepository->findOneByHashRaw($hashEvent->getHash());
            if ($link !== []) {
                /** @var LuxletterLinkProcessEvent $event */
                $event = $this->eventDispatcher->dispatch(new LuxletterLinkProcessEvent($link, $request, $handler));
                $link = $event->getLink();
                $this->eventDispatcher->dispatch(new LuxletterLinkLuxIdentificationEvent($link));
                $this->logService->logLinkOpening($link);
                return new RedirectResponse($link['target'], 302);
            }
        }
        return $handler->handle($request);
    }

    /**
     * Only the query string is respected - the hash is never expected in POST data or cookies
     *
     * @param ServerRequestInterface $request
     * @return LuxletterLinkGetHashEvent
     */
    protected function getHashEvent(ServerRequestInterface $request): LuxletterLinkGetHashEvent
    {
        $hash = $request->getQueryParams()[self::PARAMETER_NAME] ?? null;
        /** @var LuxletterLinkGetHashEvent $event */
        $event = $this->eventDispatcher->dispatch(new LuxletterLinkGetHashEvent(is_string($hash) ? $hash : null));
        return $event;
    }
}

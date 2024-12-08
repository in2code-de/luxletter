<?php

declare(strict_types=1);
namespace In2code\Luxletter\Middleware;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Utility\CookieUtility;
use In2code\Luxletter\Domain\Repository\LinkRepository;
use In2code\Luxletter\Domain\Service\LogService;
use In2code\Luxletter\Events\LuxletterLinkGetHashEvent;
use In2code\Luxletter\Events\LuxletterLinkLuxIdentificationEvent;
use In2code\Luxletter\Events\LuxletterLinkProcessEvent;
use In2code\Luxletter\Utility\ExtensionUtility;
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
    public function __construct(
        readonly private EventDispatcherInterface $eventDispatcher,
        readonly private LinkRepository $linkRepository,
        readonly private LogService $logService
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
        if ($this->isLuxletterLink()) {
            $link = $this->linkRepository->findOneByHashRaw($this->getHash());
            if ($link !== []) {
                /** @var LuxletterLinkProcessEvent $event */
                $event = $this->eventDispatcher->dispatch(new LuxletterLinkProcessEvent($link, $request, $handler));
                $link = $event->getLink();
                $this->luxIdentification($link);
                $this->logService->logLinkOpening($link);
                return new RedirectResponse($link['target'], 302);
            }
        }
        return $handler->handle($request);
    }

    protected function isLuxletterLink(): bool
    {
        /** @var LuxletterLinkGetHashEvent $event */
        $event = $this->eventDispatcher->dispatch(new LuxletterLinkGetHashEvent($_REQUEST['luxletterlink'] ?? null));
        return $event->isHashGiven();
    }

    protected function getHash(): ?string
    {
        /** @var LuxletterLinkGetHashEvent $event */
        $event = $this->eventDispatcher->dispatch(new LuxletterLinkGetHashEvent($_REQUEST['luxletterlink'] ?? null));
        return $event->getHash();
    }

    /**
     * Identification of user in EXT:lux: Set a session cookie that can be removed once it was read by lux
     *
     * @param array $link
     * @return void
     * @throws ExceptionPackage
     */
    protected function luxIdentification(array $link): void
    {
        /** @var LuxletterLinkLuxIdentificationEvent $event */
        $event = $this->eventDispatcher->dispatch(new LuxletterLinkLuxIdentificationEvent($link));
        if (ExtensionUtility::isLuxAvailable() && $event->isIdentification()) {
            $link = $event->getLink();
            CookieUtility::setCookie('luxletterlinkhash', $link['hash']);
        }
    }
}

<?php

declare(strict_types=1);
namespace In2code\Luxletter\Middleware;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use In2code\Lux\Utility\CookieUtility;
use In2code\Luxletter\Domain\Model\Link;
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
use TYPO3\CMS\Core\Package\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * Class LuxletterLink
 * to redirect a luxletterlink to its target and track the click before
 */
class LuxletterLink implements MiddlewareInterface
{
    public function __construct(
        readonly private EventDispatcherInterface $eventDispatcher,
        readonly private LinkRepository $linkRepository
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Exception
     * @throws IllegalObjectTypeException
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isLuxletterLink()) {
            /** @var Link $link */
            $link = $this->linkRepository->findOneByHash($this->getHash());
            if ($link !== null) {
                /** @var LuxletterLinkProcessEvent $event */
                $event = $this->eventDispatcher->dispatch(GeneralUtility::makeInstance(
                    LuxletterLinkProcessEvent::class,
                    $link,
                    $request,
                    $handler
                ));
                $link = $event->getLink();
                $this->luxIdentification($link);
                $logService = GeneralUtility::makeInstance(LogService::class);
                $logService->logLinkOpening($link);
                return new RedirectResponse($link->getTarget(), 302);
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
     * @param Link $link
     * @return void
     * @throws Exception
     */
    protected function luxIdentification(Link $link): void
    {
        /** @var LuxletterLinkLuxIdentificationEvent $event */
        $event = $this->eventDispatcher->dispatch(new LuxletterLinkLuxIdentificationEvent($link));
        if (ExtensionUtility::isLuxAvailable() && $event->isIdentification()) {
            CookieUtility::setCookie('luxletterlinkhash', $link->getHash());
        }
    }
}

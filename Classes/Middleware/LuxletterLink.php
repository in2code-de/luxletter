<?php

declare(strict_types=1);
namespace In2code\Luxletter\Middleware;

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
use TYPO3\CMS\Extbase\Object\Exception as ExceptionExtbase;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class LuxletterLink
 * to redirect a luxletterlink to its target and track the click before
 */
class LuxletterLink implements MiddlewareInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws ExceptionExtbase
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isLuxletterLink()) {
            $linkRepository = GeneralUtility::makeInstance(LinkRepository::class);
            /** @var Link $link */
            $link = $linkRepository->findOneByHash($this->getHash());
            /** @var LuxletterLinkProcessEvent $event */
            $event = $this->eventDispatcher->dispatch(GeneralUtility::makeInstance(
                LuxletterLinkProcessEvent::class,
                $link,
                $request,
                $handler
            ));
            $link = $event->getLink();
            if ($link !== null) {
                $this->luxIdentification($link);
                $logService = GeneralUtility::makeInstance(LogService::class);
                $logService->logLinkOpening($link);
                return new RedirectResponse($link->getTarget(), 302);
            }
        }
        return $handler->handle($request);
    }

    /**
     * @return bool
     */
    protected function isLuxletterLink(): bool
    {
        return $this->getHash() !== null;
    }

    /**
     * @return string|null
     */
    protected function getHash(): ?string
    {
        $hash = GeneralUtility::_GP('luxletterlink');
        /** @var LuxletterLinkGetHashEvent $event */
        $event = $this->eventDispatcher->dispatch(
            GeneralUtility::makeInstance(LuxletterLinkGetHashEvent::class, $hash)
        );
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
        $event = $this->eventDispatcher->dispatch(
            GeneralUtility::makeInstance(LuxletterLinkLuxIdentificationEvent::class, $link)
        );
        if (ExtensionUtility::isLuxAvailable('7.0.0') && $event->isIdentification()) {
            CookieUtility::setCookie('luxletterlinkhash', $link->getHash());
        }
    }
}

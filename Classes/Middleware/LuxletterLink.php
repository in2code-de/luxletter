<?php
declare(strict_types=1);
namespace In2code\Luxletter\Middleware;

use In2code\Lux\Utility\CookieUtility;
use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Repository\LinkRepository;
use In2code\Luxletter\Domain\Service\LogService;
use In2code\Luxletter\Signal\SignalTrait;
use In2code\Luxletter\Utility\ExtensionUtility;
use In2code\Luxletter\Utility\ObjectUtility;
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
    use SignalTrait;

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
            $linkRepository = ObjectUtility::getObjectManager()->get(LinkRepository::class);
            /** @var Link $link */
            $link = $linkRepository->findOneByHash($this->getHash());
            $this->signalDispatch(__CLASS__, __FUNCTION__, [$link, $request, $handler]);
            if ($link !== null) {
                $this->luxIdentification($link);
                $logService = ObjectUtility::getObjectManager()->get(LogService::class);
                $logService->logLinkOpening($link);
                return new RedirectResponse($link->getTarget(), 302);
            }
        }
        return $handler->handle($request);
    }

    /**
     * @return bool
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function isLuxletterLink(): bool
    {
        return $this->getHash() !== null;
    }

    /**
     * @return string|null
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function getHash(): ?string
    {
        $hash = GeneralUtility::_GP('luxletterlink');
        $this->signalDispatch(__CLASS__, __FUNCTION__, [&$hash]);
        return $hash;
    }

    /**
     * Identification of user in EXT:lux: Set a session cookie that can be removed once it was read by lux
     *
     * @param Link $link
     * @return void
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws Exception
     */
    protected function luxIdentification(Link $link): void
    {
        $identification = true;
        $this->signalDispatch(__CLASS__, __FUNCTION__, [&$identification, $link]);
        if (ExtensionUtility::isLuxAvailable('7.0.0') && $identification === true) {
            CookieUtility::setCookie('luxletterlinkhash', $link->getHash());
        }
    }
}

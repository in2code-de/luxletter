<?php
declare(strict_types=1);
namespace In2code\Luxletter\Middleware;

use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Repository\LinkRepository;
use In2code\Luxletter\Domain\Service\LogService;
use In2code\Luxletter\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * Class LuxletterLink
 */
class LuxletterLink implements MiddlewareInterface
{

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws IllegalObjectTypeException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isLuxletterLink()) {
            $linkRepository = ObjectUtility::getObjectManager()->get(LinkRepository::class);
            /** @var Link $link */
            $link = $linkRepository->findOneByHash($this->getHash());
            if ($link !== null) {
                $logService = ObjectUtility::getObjectManager()->get(LogService::class);
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
        return GeneralUtility::_GP('luxletterlink');
    }
}

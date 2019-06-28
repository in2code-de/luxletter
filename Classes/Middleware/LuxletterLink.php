<?php
declare(strict_types=1);
namespace In2code\Luxletter\Middleware;

use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Repository\LinkRepository;
use In2code\Luxletter\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class LuxletterLink
 */
class LuxletterLink implements MiddlewareInterface
{

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        if ($this->isLuxletterLink()) {
            $linkRepository = ObjectUtility::getObjectManager()->get(LinkRepository::class);
            /** @var Link $link */
            $link = $linkRepository->findOneByHash($this->getHash());
            if ($link !== null) {
                return new RedirectResponse($link->getTarget(), 302);
            }
        }
        return $response;
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

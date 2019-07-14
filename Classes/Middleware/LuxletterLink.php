<?php
declare(strict_types=1);
namespace In2code\Luxletter\Middleware;

use In2code\Lux\Domain\Factory\VisitorFactory;
use In2code\Lux\Domain\Tracker\AttributeTracker;
use In2code\Lux\Utility\CookieUtility;
use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Repository\LinkRepository;
use In2code\Luxletter\Domain\Service\LogService;
use In2code\Luxletter\Utility\ExtensionUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * Class LuxletterLink
 * to redirect a luxletterlink to its target and track the click before
 */
class LuxletterLink implements MiddlewareInterface
{

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->isLuxletterLink()) {
            $linkRepository = ObjectUtility::getObjectManager()->get(LinkRepository::class);
            /** @var Link $link */
            $link = $linkRepository->findOneByHash($this->getHash());
            $this->luxIdentification($link);
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

    /**
     * Identification of user in EXT:lux
     *
     * @param Link $link
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Exception
     */
    protected function luxIdentification(Link $link): void
    {
        if (ExtensionUtility::isLuxAvailable('5.0.0')) {
            $idCookie = CookieUtility::getLuxId();
            if ($idCookie === '') {
                $idCookie = CookieUtility::setLuxId();
            }
            $visitorFactory = ObjectUtility::getObjectManager()->get(VisitorFactory::class, $idCookie);
            $visitor = $visitorFactory->getVisitor();
            $attributeTracker = ObjectUtility::getObjectManager()->get(
                AttributeTracker::class,
                $visitor,
                AttributeTracker::CONTEXT_LUXLETTERLINK
            );
            $attributeTracker->addAttribute('email', $link->getUser()->getEmail());
        }
    }
}

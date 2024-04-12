<?php

declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service\BodytextManipulation;

use DOMDocument;
use DOMElement;
use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\LinkRepository;
use In2code\Luxletter\Domain\Service\SiteService;
use In2code\Luxletter\Events\HashLinkEvent;
use In2code\Luxletter\Events\HashLinksEvent;
use In2code\Luxletter\Exception\ArgumentMissingException;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Utility\StringUtility;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * Class LinkHashing to rewrite links in newsletter to be able to track link clicks
 */
class LinkHashing
{
    protected ?Newsletter $newsletter = null;
    protected ?User $user = null;
    protected ?LinkRepository $linkRepository = null;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(Newsletter $newsletter, User $user)
    {
        $this->newsletter = $newsletter;
        $this->user = $user;
        $this->linkRepository = GeneralUtility::makeInstance(LinkRepository::class);
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcherInterface::class);
    }

    /**
     * @param string $content
     * @return string
     * @throws ArgumentMissingException
     * @throws IllegalObjectTypeException
     * @throws MisconfigurationException
     * @throws SiteNotFoundException
     */
    public function hashLinks(string $content): string
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($content);
        $links = $dom->getElementsByTagName('a');
        foreach ($links as $link) {
            $this->hashLink($link);
        }
        $this->eventDispatcher->dispatch(GeneralUtility::makeInstance(HashLinksEvent::class, $dom, $this));
        return $dom->saveHTML();
    }

    /**
     * Try to hash absolute urls but no a-tags with data-luxletter-parselink="false"
     *
     * @param DOMElement $aTag
     * @return void
     * @throws IllegalObjectTypeException
     * @throws MisconfigurationException
     * @throws ArgumentMissingException
     * @throws SiteNotFoundException
     */
    protected function hashLink(DOMElement $aTag): void
    {
        $href = $aTag->getAttribute('href');
        $href = $this->convertToAbsoluteHref($href);
        if (StringUtility::isValidUrl($href)) {
            if ($aTag->getAttribute('data-luxletter-parselink') !== 'false') {
                $link = GeneralUtility::makeInstance(Link::class)
                    ->setNewsletter($this->newsletter)
                    ->setUser($this->user)
                    ->setTarget($href);
                $aTag->setAttribute('href', $link->getUriFromHash());
                $this->eventDispatcher->dispatch(GeneralUtility::makeInstance(HashLinkEvent::class, $link, $this));
                $this->linkRepository->add($link);
            } else {
                $aTag->removeAttribute('data-luxletter-parselink');
            }
        }
    }

    /**
     * Convert href with leading slash to an absolute url
     *
     * @param string $href
     * @return string
     * @throws MisconfigurationException
     */
    protected function convertToAbsoluteHref(string $href): string
    {
        if (StringUtility::startsWith($href, '/')) {
            $href = ltrim($href, '/');
            $siteService = GeneralUtility::makeInstance(SiteService::class);
            $href = $siteService->getDomainFromSite(
                $this->newsletter->getConfiguration()->getSiteConfiguration()
            ) . $href;
        }
        return $href;
    }
}

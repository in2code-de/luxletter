<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\LinkRepository;
use In2code\Luxletter\Exception\ArgumentMissingException;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Signal\SignalTrait;
use In2code\Luxletter\Utility\ObjectUtility;
use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class LinkHashingService to rewrite links in newsletter to be able to track link clicks
 */
class LinkHashingService
{
    use SignalTrait;

    /**
     * @var Newsletter
     */
    protected $newsletter = null;

    /**
     * @var User
     */
    protected $user = null;

    /**
     * @var LinkRepository
     */
    protected $linkRepository = null;

    /**
     * LinkHashingService constructor.
     * @param Newsletter $newsletter
     * @param User $user
     * @throws Exception
     */
    public function __construct(Newsletter $newsletter, User $user)
    {
        $this->newsletter = $newsletter;
        $this->user = $user;
        $this->linkRepository = ObjectUtility::getObjectManager()->get(LinkRepository::class);
    }

    /**
     * @param string $content
     * @return string
     * @throws ArgumentMissingException
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws MisconfigurationException
     */
    public function hashLinks(string $content): string
    {
        $dom = new \DOMDocument;
        @$dom->loadHTML($content);
        $links = $dom->getElementsByTagName('a');
        foreach ($links as $link) {
            $this->hashLink($link);
        }
        $this->signalDispatch(__CLASS__, __FUNCTION__, [$dom, $this]);
        return $dom->saveHTML();
    }

    /**
     * Try to hash absolute urls but no a-tags with data-luxletter-parselink="false"
     *
     * @param \DOMElement $aTag
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws MisconfigurationException
     * @throws ArgumentMissingException
     * @throws Exception
     */
    protected function hashLink(\DOMElement $aTag): void
    {
        $href = $aTag->getAttribute('href');
        $href = $this->convertToAbsoluteHref($href);
        if (StringUtility::isValidUrl($href)) {
            if ($aTag->getAttribute('data-luxletter-parselink') !== 'false') {
                /** @var Link $link */
                $link = ObjectUtility::getObjectManager()->get(Link::class)
                    ->setNewsletter($this->newsletter)
                    ->setUser($this->user)
                    ->setTarget($href);
                $aTag->setAttribute('href', $link->getUriFromHash());
                $this->signalDispatch(__CLASS__, __FUNCTION__, [$link, $this]);
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
     * @throws SiteNotFoundException
     */
    protected function convertToAbsoluteHref(string $href): string
    {
        if (StringUtility::startsWith($href, '/')) {
            $href = ltrim($href, '/');
            /** @var SiteService $siteService */
            $siteService = GeneralUtility::makeInstance(SiteService::class);
            $href = $siteService->getDomainFromSite(
                $this->newsletter->getConfiguration()->getSiteConfiguration()
            ) . $href;
        }
        return $href;
    }
}

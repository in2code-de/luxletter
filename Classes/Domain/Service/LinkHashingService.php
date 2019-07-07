<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Repository\LinkRepository;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * Class LinkHashingService to rewrite links in newsletter to be able to track link clicks
 */
class LinkHashingService
{
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
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     */
    public function hashLinks(string $content): string
    {
        $dom = new \DOMDocument;
        @$dom->loadHTML($content);
        $links = $dom->getElementsByTagName('a');
        foreach ($links as $link) {
            $this->hashLink($link);
        }
        return $dom->saveHTML();
    }

    /**
     * Try to hash absolute urls but no a-tags with data-luxletter-parselink="false"
     *
     * @param \DOMElement $aTag
     * @return void
     * @throws IllegalObjectTypeException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function hashLink(\DOMElement $aTag): void
    {
        $href = $aTag->getAttribute('href');
        $href = $this->convertToAbsoluteHref($href);
        if (StringUtility::isValidUrl($href)) {
            if ($aTag->getAttribute('data-luxletter-parselink') !== 'false') {
                $link = ObjectUtility::getObjectManager()->get(Link::class)
                    ->setNewsletter($this->newsletter)
                    ->setUser($this->user)
                    ->setTarget($href);
                $aTag->setAttribute('href', $link->getUriFromHash());
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
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    protected function convertToAbsoluteHref(string $href): string
    {
        if (StringUtility::startsWith($href, '/')) {
            $href = ConfigurationUtility::getDomain() . $href;
        }
        return $href;
    }
}

<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Utility\ObjectUtility;
use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

/**
 * Class ParseNewsletterUrlService
 */
class ParseNewsletterUrlService
{
    /**
     * @var string
     */
    protected $url = '';

    /**
     * ParseNewsletterUrlService constructor.
     * @param string $origin
     */
    public function __construct(string $origin)
    {
        $url = '';
        if (MathUtility::canBeInterpretedAsInteger($origin)) {
            $urlSrervice = ObjectUtility::getObjectManager()->get(FrontendUrlService::class);
            $url = $urlSrervice->getTypolinkFromParameter($origin);
        } elseif (StringUtility::isValidUrl($origin)) {
            $url = $origin;
        }
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getParsedContent(): string
    {
        if ($this->url === '') {
            throw new \LogicException('Given URL was invalid and was not parsed', 1560709687);
        }
        $string = GeneralUtility::getUrl($this->url);
        if ($string === false) {
            throw new \DomainException('Given URL could not be parsed and accessed', 1560709791);
        }
        return $string;
    }
}

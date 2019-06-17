<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Domain\Factory\UserFactory;
use In2code\Luxletter\Utility\ObjectUtility;
use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

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
     * @var string
     */
    protected $containerFile = 'EXT:luxletter/Resources/Private/Templates/Mail/NewsletterContainer.html';

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
        $content = $this->getNewsletterContainer($this->getContentFromOrigin());
        return $content;
    }

    /**
     * @param string $content
     * @return string
     */
    protected function getNewsletterContainer(string $content): string
    {
        $standaloneView = ObjectUtility::getObjectManager()->get(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->containerFile));
        $standaloneView->assignMultiple(['content' => $content, 'user' => UserFactory::getDummyUser()]);
        return $standaloneView->render();
    }

    /**
     * @return mixed|string
     */
    protected function getContentFromOrigin()
    {
        if ($this->url === '') {
            throw new \LogicException('Given URL was invalid and was not parsed', 1560709687);
        }
        $string = GeneralUtility::getUrl($this->url);
        $string = $this->getBodyFromHtml($string);
        if ($string === false) {
            throw new \DomainException('Given URL could not be parsed and accessed', 1560709791);
        }
        return $string;
    }

    /**
     * @param string $string
     * @return string
     */
    protected function getBodyFromHtml(string $string): string
    {
        try {
            $document = new \DOMDocument;
            libxml_use_internal_errors(true);
            $document->loadHtml($string);
            libxml_use_internal_errors(false);
            $xpath = new \DOMXpath($document);
            $result = '';
            foreach ($xpath->evaluate('//body/node()') as $node) {
                $result .= $document->saveHtml($node);
            }
            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $exception) {
        }
        return $string;
    }
}

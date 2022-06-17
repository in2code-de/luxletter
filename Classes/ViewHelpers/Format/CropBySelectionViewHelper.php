<?php
declare(strict_types = 1);
namespace In2code\Luxletter\ViewHelpers\Format;

use DOMDocument;
use DOMNode;
use DomXPath;
use In2code\Luxletter\Utility\DomDocumentUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Class CropBySelectionViewHelper
 * @noinspection PhpUnused
 */
class CropBySelectionViewHelper extends AbstractViewHelper
{
    /**
     * @var bool
     */
    protected $escapeChildren = false;

    /**
     * @var bool
     */
    protected $escapeOutput = false;

    /**
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('limit', 'int', 'max number of characters', false, 240);
        $this->registerArgument('append', 'string', 'append a text', false, ' ...');
        $this->registerArgument('classNameToSelect', 'string', 'a class that identifed bodytext', false, 'ce-bodytext');
    }

    /**
     * @return string
     * @throws Exception
     */
    public function render(): string
    {
        $dom = new DOMDocument();
        @$dom->loadHTML(
            DomDocumentUtility::wrapHtmlWithMainTags($this->renderChildren()),
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        $xpath = new DomXPath($dom);
        $nodeList = $xpath->query('//div[@class="' . $this->arguments['classNameToSelect'] . '"]');
        $node = $nodeList->item(0);
        if ($node !== null) {
            $htmlBodytext = $this->getHtmlOfDomElement($node);
            $node->nodeValue = '{{bodytext}}';
            $html = DomDocumentUtility::stripMainTagsFromHtml($dom->saveHTML());
            return str_replace('{{bodytext}}', $this->cropText($htmlBodytext), $html);
        }
        return $this->renderChildren();
    }

    /**
     * @param DOMNode $element
     * @return string
     */
    protected function getHtmlOfDomElement(DOMNode $element): string
    {
        $innerHTML = '';
        foreach ($element->childNodes as $child) {
            $innerHTML .= $element->ownerDocument->saveHTML($child);
        }
        return $innerHTML;
    }

    /**
     * @param string $string
     * @return string
     * @throws Exception
     */
    protected function cropText(string $string): string
    {
        if (!empty($string)) {
            $contentObject = ObjectUtility::getContentObject();
            /** @noinspection PhpInternalEntityUsedInspection */
            $string = $contentObject->cropHTML(
                $string,
                $this->arguments['limit'] . '|' . $this->arguments['append'] . '|1'
            );
        }
        return $string;
    }
}

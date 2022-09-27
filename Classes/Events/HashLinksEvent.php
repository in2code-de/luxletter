<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

use DOMDocument;
use In2code\Luxletter\Domain\Service\BodytextManipulation\LinkHashing;

final class HashLinksEvent
{
    /**
     * @var DOMDocument
     */
    protected $dom;

    /**
     * @var LinkHashing
     */
    protected $linkHashing;

    /**
     * @param DOMDocument $dom
     * @param LinkHashing $linkHashing
     */
    public function __construct(DOMDocument $dom, LinkHashing $linkHashing)
    {
        $this->dom = $dom;
        $this->linkHashing = $linkHashing;
    }

    /**
     * @return DOMDocument
     */
    public function getDom(): DOMDocument
    {
        return $this->dom;
    }

    /**
     * @return LinkHashing
     */
    public function getLinkHashing(): LinkHashing
    {
        return $this->linkHashing;
    }
}

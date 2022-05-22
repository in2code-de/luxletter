<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Model\Link;
use In2code\Luxletter\Domain\Service\BodytextManipulation\LinkHashing;

final class HashLinkEvent
{
    /**
     * @var Link
     */
    protected $link;

    /**
     * @var LinkHashing
     */
    protected $linkHashing;

    /**
     * @param Link $link
     * @param LinkHashing $linkHashing
     */
    public function __construct(Link $link, LinkHashing $linkHashing)
    {
        $this->link = $link;
        $this->linkHashing = $linkHashing;
    }

    /**
     * @return Link
     */
    public function getLink(): Link
    {
        return $this->link;
    }

    /**
     * @return LinkHashing
     */
    public function getLinkHashing(): LinkHashing
    {
        return $this->linkHashing;
    }
}

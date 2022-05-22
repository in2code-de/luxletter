<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Model\Link;

final class LuxletterLinkLuxIdentificationEvent
{
    /**
     * @var Link
     */
    protected $link;

    /**
     * @var bool
     */
    protected $identification = true;

    /**
     * @param Link $link
     */
    public function __construct(Link $link)
    {
        $this->link = $link;
    }

    /**
     * @return Link
     */
    public function getLink(): Link
    {
        return $this->link;
    }

    /**
     * @param Link $link
     * @return LuxletterLinkLuxIdentificationEvent
     */
    public function setLink(Link $link): LuxletterLinkLuxIdentificationEvent
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIdentification(): bool
    {
        return $this->identification;
    }

    /**
     * @param bool $identification
     * @return LuxletterLinkLuxIdentificationEvent
     */
    public function setIdentification(bool $identification): LuxletterLinkLuxIdentificationEvent
    {
        $this->identification = $identification;
        return $this;
    }
}

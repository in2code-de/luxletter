<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Model\Link;

final class LuxletterLinkLuxIdentificationEvent
{
    protected Link $link;
    protected bool $identification = true;

    public function __construct(Link $link)
    {
        $this->link = $link;
    }

    public function getLink(): Link
    {
        return $this->link;
    }

    public function setLink(Link $link): LuxletterLinkLuxIdentificationEvent
    {
        $this->link = $link;
        return $this;
    }

    public function isIdentification(): bool
    {
        return $this->identification;
    }

    public function setIdentification(bool $identification): LuxletterLinkLuxIdentificationEvent
    {
        $this->identification = $identification;
        return $this;
    }
}

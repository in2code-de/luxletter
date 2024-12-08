<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

final class LuxletterLinkLuxIdentificationEvent
{
    protected bool $identification = true;

    public function __construct(protected array $link)
    {
    }

    public function getLink(): array
    {
        return $this->link;
    }

    public function setLink(array $link): LuxletterLinkLuxIdentificationEvent
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

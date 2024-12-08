<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

final class LuxletterLinkGetHashEvent
{
    protected ?string $hash = null;

    public function __construct(string $hash = null)
    {
        $this->hash = $hash;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function isHashGiven(): bool
    {
        return $this->getHash() !== null;
    }

    public function setHash(string $hash = null): LuxletterLinkGetHashEvent
    {
        $this->hash = $hash;
        return $this;
    }
}

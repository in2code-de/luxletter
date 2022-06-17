<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Events;

final class LuxletterLinkGetHashEvent
{
    /**
     * @var string|null
     */
    protected $hash;

    /**
     * @param string|null $hash
     */
    public function __construct(string $hash = null)
    {
        $this->hash = $hash;
    }

    /**
     * @return string|null
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string|null $hash
     * @return LuxletterLinkGetHashEvent
     */
    public function setHash(string $hash = null): LuxletterLinkGetHashEvent
    {
        $this->hash = $hash;
        return $this;
    }
}

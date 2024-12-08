<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LuxletterLinkProcessEvent
{
    /**
     * @param array $link
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     */
    public function __construct(
        protected array $link,
        protected ServerRequestInterface $request,
        protected RequestHandlerInterface $handler
    ) {
    }

    public function getLink(): array
    {
        return $this->link;
    }

    public function setLink(array $link): LuxletterLinkProcessEvent
    {
        $this->link = $link;
        return $this;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getHandler(): RequestHandlerInterface
    {
        return $this->handler;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Model\Link;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LuxletterLinkProcessEvent
{
    /**
     * @var Link
     */
    protected $link;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var RequestHandlerInterface
     */
    protected $handler;

    /**
     * @param Link $link
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     */
    public function __construct(Link $link, ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $this->link = $link;
        $this->request = $request;
        $this->handler = $handler;
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
     * @return LuxletterLinkProcessEvent
     */
    public function setLink(Link $link): LuxletterLinkProcessEvent
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @return RequestHandlerInterface
     */
    public function getHandler(): RequestHandlerInterface
    {
        return $this->handler;
    }
}

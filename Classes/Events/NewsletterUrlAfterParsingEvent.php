<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;

final class NewsletterUrlAfterParsingEvent
{
    /**
     * @var string
     */
    protected $content;

    /**
     * @var NewsletterUrl
     */
    protected $newsletterUrl;

    public function __construct(string $content, NewsletterUrl $newsletterUrl)
    {
        $this->content = $content;
        $this->newsletterUrl = $newsletterUrl;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    public function getNewsletterUrl(): NewsletterUrl
    {
        return $this->newsletterUrl;
    }
}

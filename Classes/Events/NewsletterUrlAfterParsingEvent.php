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

    /**
     * @param string $content
     * @param NewsletterUrl $newsletterUrl
     */
    public function __construct(string $content, NewsletterUrl $newsletterUrl)
    {
        $this->content = $content;
        $this->newsletterUrl = $newsletterUrl;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return NewsletterUrl
     */
    public function getNewsletterUrl(): NewsletterUrl
    {
        return $this->newsletterUrl;
    }
}

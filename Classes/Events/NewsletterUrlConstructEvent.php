<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;

final class NewsletterUrlConstructEvent
{
    /**
     * @var NewsletterUrl
     */
    protected $newsletterUrl;

    /**
     * @param NewsletterUrl $newsletterUrl
     */
    public function __construct(NewsletterUrl $newsletterUrl)
    {
        $this->newsletterUrl = $newsletterUrl;
    }

    /**
     * @return NewsletterUrl
     */
    public function getNewsletterUrl(): NewsletterUrl
    {
        return $this->newsletterUrl;
    }
}

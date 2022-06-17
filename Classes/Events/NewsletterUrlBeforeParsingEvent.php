<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;

final class NewsletterUrlBeforeParsingEvent
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var NewsletterUrl
     */
    protected $newsletterUrl;

    /**
     * @param User $user
     * @param NewsletterUrl $newsletterUrl
     */
    public function __construct(User $user, NewsletterUrl $newsletterUrl)
    {
        $this->user = $user;
        $this->newsletterUrl = $newsletterUrl;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return NewsletterUrl
     */
    public function getNewsletterUrl(): NewsletterUrl
    {
        return $this->newsletterUrl;
    }
}

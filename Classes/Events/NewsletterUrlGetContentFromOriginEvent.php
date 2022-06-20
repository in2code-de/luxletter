<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;

final class NewsletterUrlGetContentFromOriginEvent
{
    /**
     * @var string
     */
    protected $string;

    /**
     * @var $user
     */
    protected $user;

    /**
     * @var NewsletterUrl
     */
    protected $newsletterUrl;

    /**
     * @param string $string
     * @param User $user
     * @param NewsletterUrl $newsletterUrl
     */
    public function __construct(string $string, User $user, NewsletterUrl $newsletterUrl)
    {
        $this->string = $string;
        $this->user = $user;
        $this->newsletterUrl = $newsletterUrl;
    }

    /**
     * @return string
     */
    public function getString(): string
    {
        return $this->string;
    }

    /**
     * @param string $string
     * @return NewsletterUrlGetContentFromOriginEvent
     */
    public function setString(string $string): NewsletterUrlGetContentFromOriginEvent
    {
        $this->string = $string;
        return $this;
    }

    /**
     * @return mixed
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

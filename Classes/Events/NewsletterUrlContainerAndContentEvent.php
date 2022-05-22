<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;

final class NewsletterUrlContainerAndContentEvent
{
    /**
     * @var string
     */
    protected $content;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var array
     */
    protected $configuration;

    /**
     * @var string
     */
    protected $html;

    /**
     * @var NewsletterUrl
     */
    protected $newsletterUrl;

    /**
     * @param string $content
     * @param array $configuration
     * @param User $user
     * @param string $html
     * @param NewsletterUrl $newsletterUrl
     */
    public function __construct(
        string $content,
        array $configuration,
        User $user,
        string $html,
        NewsletterUrl $newsletterUrl
    ) {
        $this->content = $content;
        $this->configuration = $configuration;
        $this->user = $user;
        $this->html = $html;
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
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return array
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * @param string $html
     * @return NewsletterUrlContainerAndContentEvent
     */
    public function setHtml(string $html): NewsletterUrlContainerAndContentEvent
    {
        $this->html = $html;
        return $this;
    }

    /**
     * @return NewsletterUrl
     */
    public function getNewsletterUrl(): NewsletterUrl
    {
        return $this->newsletterUrl;
    }
}

<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Domain\Service\Parsing\NewsletterUrl;
use TYPO3\CMS\Fluid\View\StandaloneView;

final class NewsletterUrlContainerAndContentPostParsingEvent
{
    /**
     * @var StandaloneView
     */
    protected $standaloneView;

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
     * @var NewsletterUrl
     */
    protected $newsletterUrl;

    /**
     * @param StandaloneView $standaloneView
     * @param string $content
     * @param array $configuration
     * @param User $user
     * @param NewsletterUrl $newsletterUrl
     */
    public function __construct(
        StandaloneView $standaloneView,
        string $content,
        array $configuration,
        User $user,
        NewsletterUrl $newsletterUrl
    ) {
        $this->standaloneView = $standaloneView;
        $this->content = $content;
        $this->configuration = $configuration;
        $this->user = $user;
        $this->newsletterUrl = $newsletterUrl;
    }

    /**
     * @return StandaloneView
     */
    public function getStandaloneView(): StandaloneView
    {
        return $this->standaloneView;
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
     * @return NewsletterUrl
     */
    public function getNewsletterUrl(): NewsletterUrl
    {
        return $this->newsletterUrl;
    }
}

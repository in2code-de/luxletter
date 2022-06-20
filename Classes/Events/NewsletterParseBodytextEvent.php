<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Events;

final class NewsletterParseBodytextEvent
{
    /**
     * @var string
     */
    protected $bodytext;

    /**
     * @var array
     */
    protected $properties;

    /**
     * @param string $bodytext
     * @param array $properties
     */
    public function __construct(string $bodytext, array $properties)
    {
        $this->bodytext = $bodytext;
        $this->properties = $properties;
    }

    /**
     * @return string
     */
    public function getBodytext(): string
    {
        return $this->bodytext;
    }

    /**
     * @param string $bodytext
     * @return NewsletterParseBodytextEvent
     */
    public function setBodytext(string $bodytext): NewsletterParseBodytextEvent
    {
        $this->bodytext = $bodytext;
        return $this;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @param array $properties
     * @return NewsletterParseBodytextEvent
     */
    public function setProperties(array $properties): NewsletterParseBodytextEvent
    {
        $this->properties = $properties;
        return $this;
    }
}

<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Events;

final class NewsletterParseMailtextEvent
{
    /**
     * @var string
     */
    protected $text;

    /**
     * @var array
     */
    protected $properties;

    /**
     * @param string $text
     * @param array $properties
     */
    public function __construct(string $text, array $properties)
    {
        $this->text = $text;
        $this->properties = $properties;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return NewsletterParseMailtextEvent
     */
    public function setText(string $text): NewsletterParseMailtextEvent
    {
        $this->text = $text;
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
     * @return NewsletterParseMailtextEvent
     */
    public function setProperties(array $properties): NewsletterParseMailtextEvent
    {
        $this->properties = $properties;
        return $this;
    }
}

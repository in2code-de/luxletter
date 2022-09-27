<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

final class NewsletterParseSubjectEvent
{
    /**
     * @var string
     */
    protected $subject;

    /**
     * @var array
     */
    protected $properties;

    /**
     * @param string $subject
     * @param array $properties
     */
    public function __construct(string $subject, array $properties)
    {
        $this->subject = $subject;
        $this->properties = $properties;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return NewsletterParseSubjectEvent
     */
    public function setSubject(string $subject): NewsletterParseSubjectEvent
    {
        $this->subject = $subject;
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
     * @return NewsletterParseSubjectEvent
     */
    public function setProperties(array $properties): NewsletterParseSubjectEvent
    {
        $this->properties = $properties;
        return $this;
    }
}

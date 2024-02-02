<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Model\Queue;

final class BeforeBodytextIsParsedEvent
{
    protected Queue $queue;

    protected string $bodytext = '';

    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
        $this->bodytext = $queue->getNewsletter()->getBodytext();
    }

    public function getQueue(): Queue
    {
        return $this->queue;
    }

    public function getBodytext(): string
    {
        return $this->bodytext;
    }

    public function setBodytext(string $bodytext): self
    {
        $this->bodytext = $bodytext;
        return $this;
    }
}

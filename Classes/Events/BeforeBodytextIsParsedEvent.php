<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

use In2code\Luxletter\Domain\Model\Queue;

final class BeforeBodytextIsParsedEvent
{
    protected Queue $queue;

    protected string $bodytext = '';

    public function getQueue(): Queue
    {
        return $this->queue;
    }

    public function setQueue(Queue $queue): void
    {
        $this->queue = $queue;
    }

    public function getBodytext(): string
    {
        return $this->bodytext;
    }

    public function setBodytext(string $bodytext): void
    {
        $this->bodytext = $bodytext;
    }
}

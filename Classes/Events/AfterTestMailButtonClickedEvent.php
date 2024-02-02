<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

use Psr\Http\Message\ServerRequestInterface;

final class AfterTestMailButtonClickedEvent
{
    public const STATUS_SEVERITY_SUCCESS = 'alert-success';
    public const STATUS_SEVERITY_WARNING = 'alert-warning';
    public const STATUS_SEVERITY_ERROR = 'alert-danger';

    protected ServerRequestInterface $request;

    protected bool $testMailIsSendExternal = false;
    protected bool $status = false;

    protected string $statusTitle = '';
    protected string $statusMessage = '';
    protected string $statusSeverity = self::STATUS_SEVERITY_ERROR;

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function isTestMailIsSendExternal(): bool
    {
        return $this->testMailIsSendExternal;
    }

    public function setTestMailIsSendExternal(): self
    {
        $this->testMailIsSendExternal = true;
        return $this;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getStatusTitle(): string
    {
        return $this->statusTitle;
    }

    public function setStatusTitle(string $statusTitle): self
    {
        $this->statusTitle = $statusTitle;
        return $this;
    }

    public function getStatusMessage(): string
    {
        return $this->statusMessage;
    }

    public function setStatusMessage(string $statusMessage): self
    {
        $this->statusMessage = $statusMessage;
        return $this;
    }

    public function getStatusSeverity(): string
    {
        return $this->statusSeverity;
    }

    public function setStatusSeverity(string $statusSeverity): self
    {
        $this->statusSeverity = $statusSeverity;
        return $this;
    }

    public function getStatusResponse(): array
    {
        return [
            'statusTitle' => $this->getStatusTitle(),
            'statusMessage' => $this->getStatusMessage(),
            'statusSeverity' => $this->getStatusSeverity(),
        ];
    }
}

<?php

declare(strict_types=1);
namespace In2code\Luxletter\Events;

use Psr\Http\Message\ServerRequestInterface;

final class AfterTestMailButtonClickedEvent
{
    const STATUS_SEVERITY_SUCCESS = 'alert-success';
    const STATUS_SEVERITY_WARNING = 'alert-warning';
    const STATUS_SEVERITY_ERROR = 'alert-danger';

    protected bool $testMailIsSendExternal = false;

    protected bool $status = false;

    protected string $statusTitle = '';

    protected string $statusMessage = '';

    protected string $statusSeverity = self::STATUS_SEVERITY_ERROR;

    protected ServerRequestInterface $request;

    public function isTestMailIsSendExternal(): bool
    {
        return $this->testMailIsSendExternal;
    }

    public function setTestMailIsSendExternal(bool $testMailIsSendExternal): void
    {
        $this->testMailIsSendExternal = $testMailIsSendExternal;
    }

    public function getStatus(): bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function getStatusTitle(): string
    {
        return $this->statusTitle;
    }

    public function setStatusTitle(string $statusTitle): void
    {
        $this->statusTitle = $statusTitle;
    }

    public function getStatusMessage(): string
    {
        return $this->statusMessage;
    }

    public function setStatusMessage(string $statusMessage): void
    {
        $this->statusMessage = $statusMessage;
    }

    public function getStatusSeverity(): string
    {
        return $this->statusSeverity;
    }

    public function setStatusSeverity(string $statusSeverity): void
    {
        $this->statusSeverity = $statusSeverity;
    }
}

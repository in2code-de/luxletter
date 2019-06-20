<?php
declare(strict_types=1);
use In2code\Luxletter\Command\QueueCommand;

return [
    'luxletter:queue' => [
        'class' => QueueCommand::class,
        'schedulable' => true
    ]
];

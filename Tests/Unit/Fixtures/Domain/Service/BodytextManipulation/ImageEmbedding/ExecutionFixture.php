<?php

namespace In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\BodytextManipulation\ImageEmbedding;

use In2code\Luxletter\Domain\Service\BodytextManipulation\ImageEmbedding\Execution;

class ExecutionFixture extends Execution
{
    protected function getHashedFilename(string $url): string
    {
        return hash('sha256', $url);
    }
}

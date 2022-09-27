<?php

namespace In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\BodytextManipulation\ImageEmbedding;

use In2code\Luxletter\Domain\Service\BodytextManipulation\ImageEmbedding\Execution;

/**
 * Class ExecutionFixture
 */
class ExecutionFixture extends Execution
{
    /**
     * @param string $url
     * @return string
     */
    protected function getHashedFilename(string $url): string
    {
        return hash('sha256', $url);
    }
}

<?php
namespace In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\BodytextManipulation\ImageEmbedding;

use In2code\Luxletter\Domain\Service\BodytextManipulation\ImageEmbedding\Preparation;

/**
 * Class ExecutionFixture
 */
class PreparationFixture extends Preparation
{
    /**
     * @param string $url
     * @return string
     */
    protected function getHashedFilename(string $url): string
    {
        return hash('sha256', $url);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return true;
    }
}

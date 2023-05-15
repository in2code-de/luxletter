<?php

namespace In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\BodytextManipulation\ImageEmbedding;

use In2code\Luxletter\Domain\Service\BodytextManipulation\ImageEmbedding\Preparation;

class PreparationFixture extends Preparation
{
    protected function getHashedFilename(string $url): string
    {
        return hash('sha256', $url);
    }

    public function isActive(): bool
    {
        return true;
    }

    protected function getImageContent(string $url): string
    {
        return 'foo';
    }
}

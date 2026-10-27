<?php

namespace In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\BodytextManipulation\ImageEmbedding;

use In2code\Luxletter\Exception\ApiConnectionException;

class PreparationWithUnreachableImageFixture extends PreparationFixture
{
    protected function getImageContent(string $url): string
    {
        if (str_contains($url, 'unreachable')) {
            throw new ApiConnectionException('404 Not Found', 1637265924);
        }
        return parent::getImageContent($url);
    }
}

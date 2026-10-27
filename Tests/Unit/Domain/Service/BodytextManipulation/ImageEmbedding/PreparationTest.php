<?php

namespace In2code\Luxletter\Tests\Unit\Domain\Service\BodytextManipulation\ImageEmbedding;

use In2code\Luxletter\Domain\Service\BodytextManipulation\ImageEmbedding\Preparation;
use In2code\Luxletter\Tests\Unit\Fixtures\Domain\Service\BodytextManipulation\ImageEmbedding\PreparationWithUnreachableImageFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(Preparation::class)]
class PreparationTest extends UnitTestCase
{
    protected array $images = [
        'https://www.in2code.de/unreachable.png',
        'https://www.in2code.de/reachable.png',
    ];

    public function tearDown(): void
    {
        foreach ($this->images as $image) {
            $pathAndFilename = $this->getPathAndFilename($image);
            if (file_exists($pathAndFilename)) {
                unlink($pathAndFilename);
            }
        }
        parent::tearDown();
    }

    /**
     * An image that can not be loaded must not stop the parsing of the whole newsletter
     *
     * @return void
     */
    public function testStoreImagesSkipsUnreachableImagesAndStoresTheOthers(): void
    {
        (new PreparationWithUnreachableImageFixture())->storeImages(
            '<p><img src="' . $this->images[0] . '"><img src="' . $this->images[1] . '"></p>'
        );

        self::assertFileDoesNotExist($this->getPathAndFilename($this->images[0]));
        self::assertFileExists($this->getPathAndFilename($this->images[1]));
    }

    protected function getPathAndFilename(string $imageUrl): string
    {
        return GeneralUtility::getFileAbsFileName('uploads/tx_luxletter/') . hash('sha256', $imageUrl) . '.png';
    }
}

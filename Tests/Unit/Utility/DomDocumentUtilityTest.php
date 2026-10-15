<?php

namespace In2code\Luxletter\Tests\Unit\Utility;

use In2code\Luxletter\Utility\DomDocumentUtility;
use PHPUnit\Framework\Attributes\CoversClass;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(DomDocumentUtility::class)]
class DomDocumentUtilityTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testWrapHtmlWithMainTags(): void
    {
        self::assertSame(
            '<?xml encoding="utf-8" ?><html><body><p>Grüße</p></body></html>',
            DomDocumentUtility::wrapHtmlWithMainTags('<p>Grüße</p>')
        );
    }

    /**
     * @return void
     */
    public function testStripMainTagsFromHtmlRevertsWrapHtmlWithMainTags(): void
    {
        $html = '<div><p>Grüße</p></div>';
        self::assertSame(
            $html,
            DomDocumentUtility::stripMainTagsFromHtml(DomDocumentUtility::wrapHtmlWithMainTags($html))
        );
    }
}

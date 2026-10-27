<?php

namespace In2code\Luxletter\Tests\Functional\Middleware;

use In2code\Luxletter\Domain\Model\Log;
use In2code\Luxletter\Middleware\LuxletterLink;
use In2code\Luxletter\Tests\Functional\AbstractFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\TestingFramework\Core\Functional\Framework\Frontend\InternalRequest;

/**
 * Tracking links from newsletters are processed in the frontend middleware stack. cHash validation is enforced to
 * ensure the luxletterlink parameter is excluded from it.
 */
#[CoversClass(LuxletterLink::class)]
class LuxletterLinkTest extends AbstractFunctionalTestCase
{
    protected array $configurationToUseInTestInstance = [
        'FE' => [
            'cacheHash' => [
                'enforceValidation' => true,
            ],
        ],
    ];

    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/Fixtures/LuxletterLink.csv');
        $this->writeSiteConfiguration();
    }

    /**
     * @return void
     */
    public function testTrackingLinkRedirectsToTargetAndLogsClick(): void
    {
        $response = $this->executeFrontendRequestWithLuxletterlink('hash1');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('https://www.in2code.de/target', $response->getHeaderLine('location'));
        self::assertSame(
            [
                [3, 5, Log::STATUS_NEWSLETTEROPENING],
                [3, 5, Log::STATUS_LINKOPENING],
            ],
            $this->getRows(Log::TABLE_NAME, ['newsletter', 'user', 'status'])
        );
    }

    /**
     * Tracking links point to the root page of the site, which is often a shortcut to its first subpage. The click
     * must be processed before TYPO3 redirects to the shortcut target.
     *
     * @return void
     */
    public function testTrackingLinkToShortcutPageRedirectsToTargetAndLogsClick(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/LuxletterLinkShortcut.csv');
        $this->getConnectionPool()->getConnectionForTable('pages')->update(
            'pages',
            [
                'doktype' => PageRepository::DOKTYPE_SHORTCUT,
                'shortcut_mode' => PageRepository::SHORTCUT_MODE_FIRST_SUBPAGE,
            ],
            ['uid' => 1]
        );

        $response = $this->executeFrontendRequestWithLuxletterlink('hash1');

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('https://www.in2code.de/target', $response->getHeaderLine('location'));
        self::assertCount(2, $this->getRows(Log::TABLE_NAME, ['newsletter', 'user', 'status']));
    }

    /**
     * @return void
     */
    public function testUnknownHashRendersRequestedPage(): void
    {
        $response = $this->executeFrontendRequestWithLuxletterlink('unknown');

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('page content', (string)$response->getBody());
        self::assertSame([], $this->getRows(Log::TABLE_NAME, ['newsletter', 'user', 'status']));
    }

    /**
     * @param string $hash
     * @return ResponseInterface
     */
    protected function executeFrontendRequestWithLuxletterlink(string $hash): ResponseInterface
    {
        return $this->executeFrontendSubRequest(
            (new InternalRequest('https://www.example.org/'))->withQueryParameter('luxletterlink', $hash)
        );
    }
}

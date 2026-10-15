<?php

namespace In2code\Luxletter\Tests\Functional\Middleware;

use In2code\Luxletter\Domain\Model\Log;
use In2code\Luxletter\Middleware\LuxletterLink;
use In2code\Luxletter\Tests\Functional\AbstractFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\ResponseInterface;
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
     * LuxletterLink reads the hash from $_REQUEST instead of the PSR-7 request, so it has to be set additionally for
     * a sub request (superglobals are restored after each test because of backupGlobals)
     *
     * @param string $hash
     * @return ResponseInterface
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function executeFrontendRequestWithLuxletterlink(string $hash): ResponseInterface
    {
        $_REQUEST['luxletterlink'] = $hash;
        return $this->executeFrontendSubRequest(
            (new InternalRequest('https://www.example.org/'))->withQueryParameter('luxletterlink', $hash)
        );
    }
}

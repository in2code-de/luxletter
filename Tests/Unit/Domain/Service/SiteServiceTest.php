<?php

namespace In2code\Luxletter\Tests\Unit\Domain\Service;

use In2code\Luxletter\Domain\Service\SiteService;
use In2code\Luxletter\Exception\MisconfigurationException;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Routing\RouterInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(SiteService::class)]
class SiteServiceTest extends UnitTestCase
{
    protected SiteFinder&MockObject $siteFinder;
    protected SiteService $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->siteFinder = $this->createMock(SiteFinder::class);
        $this->subject = new SiteService($this->siteFinder);
    }

    public static function validBaseDataProvider(): array
    {
        return [
            'domain' => ['https://www.in2code.de/'],
            'domain with language path' => ['https://www.in2code.de/en/'],
        ];
    }

    /**
     * @param string $base
     * @return void
     */
    #[DataProvider('validBaseDataProvider')]
    public function testGetDomainFromSiteReturnsBaseOfRootPage(string $base): void
    {
        self::assertSame($base, $this->subject->getDomainFromSite($this->getSite($base)));
    }

    public static function invalidBaseDataProvider(): array
    {
        return [
            'without trailing slash' => ['https://www.in2code.de'],
            'relative base' => ['/'],
            'without scheme' => ['www.in2code.de/'],
        ];
    }

    /**
     * Links and assets in newsletters must be absolute, so a relative site base is a misconfiguration
     *
     * @param string $base
     * @return void
     */
    #[DataProvider('invalidBaseDataProvider')]
    public function testGetDomainFromSiteThrowsExceptionWithInvalidBase(string $base): void
    {
        $this->expectException(MisconfigurationException::class);
        $this->expectExceptionCode(1622832844);
        $this->subject->getDomainFromSite($this->getSite($base));
    }

    /**
     * @return void
     */
    public function testGetFrontendUrlFromParameterAddsArgumentsToDomain(): void
    {
        self::assertSame(
            'https://www.in2code.de/?luxletterlink=abc',
            $this->subject->getFrontendUrlFromParameter(
                ['luxletterlink' => 'abc'],
                $this->getSite('https://www.in2code.de/')
            )
        );
    }

    /**
     * @return void
     */
    public function testGetPageUrlFromParameterBuildsUrlToGivenPage(): void
    {
        $arguments = ['tx_luxletter_fe' => ['user' => 5]];
        $router = self::createStub(RouterInterface::class);
        $router->method('generateUri')->willReturnCallback(
            static function (int $pageIdentifier, array $parameters = []) use ($arguments): Uri {
                if ($pageIdentifier === 12 && $parameters === $arguments) {
                    return new Uri('https://www.in2code.de/unsubscribe?user=5');
                }
                return new Uri('https://www.in2code.de/');
            }
        );
        $site = self::createStub(Site::class);
        $site->method('getRootPageId')->willReturn(1);
        $site->method('getRouter')->willReturn($router);
        $this->siteFinder->expects(self::once())->method('getSiteByPageId')->with(12)->willReturn($site);

        self::assertSame(
            'https://www.in2code.de/unsubscribe?user=5',
            $this->subject->getPageUrlFromParameter(12, $arguments)
        );
    }

    /**
     * @return void
     */
    public function testGetSiteReturnsSiteOfGivenPage(): void
    {
        $site = $this->getSite('https://www.in2code.de/');
        $this->siteFinder->expects(self::once())->method('getSiteByPageId')->with(12)->willReturn($site);

        self::assertSame($site, $this->subject->getSite(12));
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testGetSiteThrowsExceptionWithoutPageIdentifierOutsideOfFrontend(): void
    {
        $GLOBALS['TSFE'] = null;

        $this->expectException(LogicException::class);
        $this->expectExceptionCode(1622813408);
        $this->subject->getSite();
    }

    /**
     * @return void
     */
    public function testGetFirstSiteReturnsFirstSite(): void
    {
        $site1 = $this->getSite('https://www.in2code.de/');
        $site2 = $this->getSite('https://www.in2code.com/');
        $this->siteFinder->method('getAllSites')->willReturn(['first' => $site1, 'second' => $site2]);

        self::assertSame($site1, $this->subject->getFirstSite());
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testGetAllowedSitesReturnsAllSitesForAdministrators(): void
    {
        $sites = ['first' => $this->getSite('https://www.in2code.de/')];
        $this->siteFinder->method('getAllSites')->willReturn($sites);
        $backendUser = self::createStub(BackendUserAuthentication::class);
        $backendUser->method('isAdmin')->willReturn(true);
        $GLOBALS['BE_USER'] = $backendUser;

        self::assertSame($sites, $this->subject->getAllowedSites());
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testGetAllowedSitesReturnsNoSitesWithoutBackendUser(): void
    {
        $this->siteFinder->method('getAllSites')->willReturn(['first' => $this->getSite('https://www.in2code.de/')]);
        unset($GLOBALS['BE_USER']);

        self::assertSame([], $this->subject->getAllowedSites());
    }

    protected function getSite(string $base): Site
    {
        $router = self::createStub(RouterInterface::class);
        $router->method('generateUri')->willReturn(new Uri($base));
        $site = self::createStub(Site::class);
        $site->method('getRootPageId')->willReturn(1);
        $site->method('getRouter')->willReturn($router);
        return $site;
    }
}

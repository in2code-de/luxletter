<?php

namespace In2code\Luxletter\Tests\Unit\Utility;

use In2code\Luxletter\Utility\BackendUserUtility;
use PHPUnit\Framework\Attributes\CoversClass;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(BackendUserUtility::class)]
class BackendUserUtilityTest extends UnitTestCase
{
    /**
     * @return void
     */
    public function testGetBackendUserAuthenticationReturnsNullWithoutBackendUser(): void
    {
        self::assertNull(BackendUserUtility::getBackendUserAuthentication());
    }

    /**
     * @return void
     */
    public function testIsBackendUserAuthenticated(): void
    {
        self::assertFalse(BackendUserUtility::isBackendUserAuthenticated());
        $this->setBackendUser(self::createStub(BackendUserAuthentication::class));
        self::assertTrue(BackendUserUtility::isBackendUserAuthenticated());
    }

    /**
     * @return void
     */
    public function testIsAdministrator(): void
    {
        self::assertFalse(BackendUserUtility::isAdministrator());

        $backendUser = self::createStub(BackendUserAuthentication::class);
        $backendUser->method('isAdmin')->willReturn(false);
        $this->setBackendUser($backendUser);
        self::assertFalse(BackendUserUtility::isAdministrator());

        $backendUser = self::createStub(BackendUserAuthentication::class);
        $backendUser->method('isAdmin')->willReturn(true);
        $this->setBackendUser($backendUser);
        self::assertTrue(BackendUserUtility::isAdministrator());
    }

    /**
     * @return void
     */
    public function testSaveValueToSessionUsesPrefixedSessionKey(): void
    {
        $backendUser = $this->createMock(BackendUserAuthentication::class);
        $backendUser
            ->expects(self::once())
            ->method('setAndSaveSessionData')
            ->with('filterlistNewsletter_luxletter', ['foo' => 'bar']);
        $this->setBackendUser($backendUser);

        BackendUserUtility::saveValueToSession('filter', 'list', 'Newsletter', ['foo' => 'bar']);
    }

    /**
     * @return void
     */
    public function testGetSessionValueReturnsArray(): void
    {
        $backendUser = $this->createMock(BackendUserAuthentication::class);
        $backendUser
            ->method('getSessionData')
            ->with('filterlistNewsletter_luxletter')
            ->willReturnOnConsecutiveCalls(['foo' => 'bar'], null);
        $this->setBackendUser($backendUser);

        self::assertSame(['foo' => 'bar'], BackendUserUtility::getSessionValue('filter', 'list', 'Newsletter'));
        self::assertSame([], BackendUserUtility::getSessionValue('filter', 'list', 'Newsletter'));
    }

    /**
     * @param BackendUserAuthentication $backendUser
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function setBackendUser(BackendUserAuthentication $backendUser): void
    {
        $GLOBALS['BE_USER'] = $backendUser;
    }
}

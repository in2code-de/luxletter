<?php

namespace In2code\Luxletter\Tests\Unit\Utility;

use In2code\Luxletter\Utility\BackendUserUtility;
use PHPUnit\Framework\Attributes\CoversClass;
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
}

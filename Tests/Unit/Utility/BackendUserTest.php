<?php

namespace In2code\Luxletter\Tests\Unit\Utility;

use In2code\Luxletter\Utility\BackendUserUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass \In2code\Luxletter\Utility\BackendUserUtility
 */
class BackendUserTest extends UnitTestCase
{
    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     * @covers ::getBackendUserAuthentication
     */
    public function testIsAbsoluteImageUrl(): void
    {
        self::assertNull(BackendUserUtility::getBackendUserAuthentication());
    }
}

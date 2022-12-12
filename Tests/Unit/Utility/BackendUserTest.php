<?php

namespace In2code\Luxletter\Tests\Unit\Utility;

use In2code\Luxletter\Utility\BackendUserUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class BackendUserTest
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

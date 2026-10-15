<?php

namespace In2code\Luxletter\Tests\Unit\Fixtures;

use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

trait EncryptionKeyTrait
{
    /**
     * Queue a stubbed core ConfigurationManager for the next ConfigurationUtility::getEncryptionKey() call
     *
     * @param string $encryptionKey
     * @return void
     */
    protected function addEncryptionKey(string $encryptionKey): void
    {
        $configurationManager = $this->createStub(ConfigurationManager::class);
        $configurationManager->method('getLocalConfigurationValueByPath')->willReturn($encryptionKey);
        GeneralUtility::addInstance(ConfigurationManager::class, $configurationManager);
    }
}

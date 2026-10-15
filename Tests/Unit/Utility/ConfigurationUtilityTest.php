<?php

namespace In2code\Luxletter\Tests\Unit\Utility;

use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Tests\Unit\Fixtures\EncryptionKeyTrait;
use In2code\Luxletter\Utility\ConfigurationUtility;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(ConfigurationUtility::class)]
class ConfigurationUtilityTest extends UnitTestCase
{
    use EncryptionKeyTrait;

    protected array $extensionConfiguration = [
        'addTypeNumToNumberLocation' => '1562349004',
        'addUnsubscribeUrlToMailHeader' => '1',
        'asynchronousQueueStorage' => '0',
        'embedImagesInNewsletter' => '0',
        'limitToContext' => '',
        'multiLanguageMode' => '0',
        'multiLanguageNewsletterPageDoktype' => '11',
        'receiverAction' => '0',
        'rewriteLinksInNewsletter' => '1',
    ];

    public static function booleanSettingsDataProvider(): array
    {
        return [
            'multiLanguageMode on' => ['isMultiLanguageModeActivated', 'multiLanguageMode', '1', true],
            'multiLanguageMode off' => ['isMultiLanguageModeActivated', 'multiLanguageMode', '0', false],
            'receiverAction on' => ['isReceiverActionActivated', 'receiverAction', '1', true],
            'receiverAction off' => ['isReceiverActionActivated', 'receiverAction', '0', false],
            'rewriteLinks on' => ['isRewriteLinksInNewsletterActivated', 'rewriteLinksInNewsletter', '1', true],
            'rewriteLinks off' => ['isRewriteLinksInNewsletterActivated', 'rewriteLinksInNewsletter', '0', false],
            'embedImages on' => ['isImageEmbeddingActivated', 'embedImagesInNewsletter', '1', true],
            'embedImages off' => ['isImageEmbeddingActivated', 'embedImagesInNewsletter', '0', false],
            'asynchronousQueue on' => ['isAsynchronousQueueStorageActivated', 'asynchronousQueueStorage', '1', true],
            'asynchronousQueue off' => ['isAsynchronousQueueStorageActivated', 'asynchronousQueueStorage', '0', false],
            // Unsubscribe header is enabled by default and can only be disabled explicitly
            'unsubscribeHeader on' => [
                'isUnsubscribeUrlToMailHeaderActivated',
                'addUnsubscribeUrlToMailHeader',
                '1',
                true,
            ],
            'unsubscribeHeader empty' => [
                'isUnsubscribeUrlToMailHeaderActivated',
                'addUnsubscribeUrlToMailHeader',
                '',
                true,
            ],
            'unsubscribeHeader off' => [
                'isUnsubscribeUrlToMailHeaderActivated',
                'addUnsubscribeUrlToMailHeader',
                '0',
                false,
            ],
        ];
    }

    /**
     * @param string $method
     * @param string $setting
     * @param string $value
     * @param bool $expected
     * @return void
     */
    #[DataProvider('booleanSettingsDataProvider')]
    public function testBooleanSettingsAreReadFromExtensionConfiguration(
        string $method,
        string $setting,
        string $value,
        bool $expected
    ): void {
        $this->setExtensionConfiguration([$setting => $value]);
        self::assertSame($expected, ConfigurationUtility::$method());
    }

    public static function multilanguageNewsletterPageDoktypeDataProvider(): array
    {
        return [
            'configured' => ['116', 116],
            'zero falls back to default' => ['0', ConfigurationUtility::PAGE_TYPE_MULTILANGUAGEMODE_DEFAULT],
            'empty falls back to default' => ['', ConfigurationUtility::PAGE_TYPE_MULTILANGUAGEMODE_DEFAULT],
        ];
    }

    /**
     * @param string $value
     * @param int $expected
     * @return void
     */
    #[DataProvider('multilanguageNewsletterPageDoktypeDataProvider')]
    public function testGetMultilanguageNewsletterPageDoktype(string $value, int $expected): void
    {
        $this->setExtensionConfiguration(['multiLanguageNewsletterPageDoktype' => $value]);
        self::assertSame($expected, ConfigurationUtility::getMultilanguageNewsletterPageDoktype());
    }

    /**
     * @return void
     */
    public function testGetTypeNumToNumberLocationReturnsInteger(): void
    {
        $this->setExtensionConfiguration(['addTypeNumToNumberLocation' => '123']);
        self::assertSame(123, ConfigurationUtility::getTypeNumToNumberLocation());
    }

    public static function isContextFittingDataProvider(): array
    {
        return [
            'no limitation' => ['', true],
            'not matching context' => ['NotExistingContext', false],
        ];
    }

    /**
     * @param string $limitToContext
     * @param bool $expected
     * @return void
     */
    #[DataProvider('isContextFittingDataProvider')]
    public function testIsContextFitting(string $limitToContext, bool $expected): void
    {
        $this->setExtensionConfiguration(['limitToContext' => $limitToContext]);
        self::assertSame($expected, ConfigurationUtility::isContextFitting());
    }

    /**
     * @return void
     */
    public function testIsContextFittingMatchesPartOfCurrentContextCaseInsensitive(): void
    {
        $currentContext = (string)Environment::getContext();
        $this->setExtensionConfiguration(['limitToContext' => strtolower(substr($currentContext, 0, 4))]);
        self::assertTrue(ConfigurationUtility::isContextFitting());
    }

    /**
     * @return void
     */
    public function testGetEncryptionKeyReturnsKeyFromConfiguration(): void
    {
        $this->addEncryptionKey('secret');
        self::assertSame('secret', ConfigurationUtility::getEncryptionKey());
    }

    /**
     * @return void
     */
    public function testGetEncryptionKeyThrowsExceptionWithoutKey(): void
    {
        $this->addEncryptionKey('');

        $this->expectException(MisconfigurationException::class);
        $this->expectExceptionCode(1562069158);
        ConfigurationUtility::getEncryptionKey();
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testGetCurrentDomainThrowsExceptionWithoutHttpHost(): void
    {
        unset($_SERVER['HTTP_HOST']);

        $this->expectException(LogicException::class);
        $this->expectExceptionCode(1622812071);
        ConfigurationUtility::getCurrentDomain();
    }

    /**
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testGetCurrentDomainReturnsSchemeAndHostWithTrailingSlash(): void
    {
        $_SERVER['HTTP_HOST'] = 'www.in2code.de';
        $_SERVER['HTTPS'] = 'on';
        $_SERVER['REQUEST_URI'] = '/folder/page?foo=bar';

        self::assertSame('https://www.in2code.de/', ConfigurationUtility::getCurrentDomain());
    }

    /**
     * @param array $configuration
     * @return void
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function setExtensionConfiguration(array $configuration): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['luxletter'] = $configuration + $this->extensionConfiguration;
    }
}

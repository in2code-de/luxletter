<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Domain\Factory\UserFactory;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Signal\SignalTrait;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use In2code\Luxletter\Utility\StringUtility;
use In2code\Luxletter\Utility\TemplateUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Routing\InvalidRouteArgumentsException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class ParseNewsletterUrlService to fill a container html with a content from a http page.
 * This is used for testmails and for storing a bodytext in a newsletter record
 */
class ParseNewsletterUrlService
{
    use SignalTrait;

    /**
     * Hold url from origin
     *
     * @var string
     */
    protected $url = '';

    /**
     * Decide if variables like {user.firstName} should be parsed with fluid or not
     *
     * @var bool
     */
    protected $parseVariables = true;

    /**
     * ParseNewsletterUrlService constructor.
     * @param string $origin can be a page uid or a complete url
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidRouteArgumentsException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws SiteNotFoundException
     */
    public function __construct(string $origin)
    {
        $url = '';
        if (MathUtility::canBeInterpretedAsInteger($origin)) {
            $arguments = [];
            $typenum = ConfigurationUtility::getTypeNumToNumberLocation();
            if ($typenum > 0) {
                $arguments = ['type' => $typenum];
            }
            $urlService = ObjectUtility::getObjectManager()->get(FrontendUrlService::class);
            $url = $urlService->getTypolinkUrlFromParameter((int)$origin, $arguments);
        } elseif (StringUtility::isValidUrl($origin)) {
            $url = $origin;
        }
        $this->signalDispatch(__CLASS__, 'constructor', [$url, $origin, $this]);
        $this->setUrl($url);
    }

    /**
     * @param User|null $user
     * @return string
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws InvalidConfigurationTypeException
     */
    public function getParsedContent(User $user = null): string
    {
        if ($user === null) {
            $userFactory = ObjectUtility::getObjectManager()->get(UserFactory::class);
            $user = $userFactory->getDummyUser();
        }
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'BeforeParsing', [$user, $this]);
        $content = $this->getNewsletterContainerAndContent($this->getContentFromOrigin($user), $user);
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'AfterParsing', [$content, $this]);
        return $content;
    }

    /**
     * @param string $content
     * @param User $user
     * @return string
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws InvalidConfigurationTypeException
     */
    protected function getNewsletterContainerAndContent(string $content, User $user): string
    {
        $templateName = 'Mail/NewsletterContainer.html';
        if ($this->isParsingActive()) {
            $configuration = ConfigurationUtility::getExtensionSettings();
            $standaloneView = ObjectUtility::getObjectManager()->get(StandaloneView::class);
            $standaloneView->setTemplateRootPaths($configuration['view']['templateRootPaths']);
            $standaloneView->setLayoutRootPaths($configuration['view']['layoutRootPaths']);
            $standaloneView->setPartialRootPaths($configuration['view']['partialRootPaths']);
            $standaloneView->setTemplate($templateName);
            $standaloneView->assignMultiple(
                [
                    'content' => $content,
                    'user' => $user
                ]
            );
            $html = $standaloneView->render();
        } else {
            $container = file_get_contents(TemplateUtility::getExistingFilePathOfTemplateFileByName($templateName));
            $html = str_replace('{content}', $content, $container);
        }
        $this->signalDispatch(__CLASS__, __FUNCTION__, [$html, $content, $user, $this]);
        return $html;
    }

    /**
     * @param User $user
     * @return string
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws InvalidConfigurationTypeException
     */
    protected function getContentFromOrigin(User $user): string
    {
        if ($this->url === '') {
            throw new \LogicException('Given URL was invalid and was not parsed', 1560709687);
        }
        $string = GeneralUtility::getUrl($this->url);
        $string = $this->getBodyFromHtml($string);
        if ($string === false) {
            throw new \DomainException('Given URL could not be parsed and accessed', 1560709791);
        }
        if ($this->isParsingActive()) {
            $parseService = ObjectUtility::getObjectManager()->get(ParseNewsletterService::class);
            $string = $parseService->parseMailText($string, ['user' => $user]);
        }
        $this->signalDispatch(__CLASS__, __FUNCTION__, [$string, $user, $this]);
        return $string;
    }

    /**
     * @param string $string
     * @return string
     */
    protected function getBodyFromHtml(string $string): string
    {
        try {
            $document = new \DOMDocument;
            libxml_use_internal_errors(true);
            $document->loadHtml($string);
            libxml_use_internal_errors(false);
            $xpath = new \DOMXpath($document);
            $result = '';
            foreach ($xpath->evaluate('//body/node()') as $node) {
                $result .= $document->saveHtml($node);
            }
            if (!empty($result)) {
                return $result;
            }
        } catch (\Exception $exception) {
        }
        return $string;
    }

    /**
     * @return bool
     */
    public function isParsingActive(): bool
    {
        return $this->parseVariables;
    }

    /**
     * @param bool $parseVariables
     * @return ParseNewsletterUrlService
     */
    public function setParseVariables(bool $parseVariables): self
    {
        $this->parseVariables = $parseVariables;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return ParseNewsletterUrlService
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }
}

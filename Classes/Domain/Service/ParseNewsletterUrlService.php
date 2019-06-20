<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Service;

use In2code\Luxletter\Domain\Factory\UserFactory;
use In2code\Luxletter\Domain\Model\User;
use In2code\Luxletter\Signal\SignalTrait;
use In2code\Luxletter\Utility\ObjectUtility;
use In2code\Luxletter\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
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
     * @var string
     */
    protected $containerFile = 'EXT:luxletter/Resources/Private/Templates/Mail/NewsletterContainer.html';

    /**
     * Decide if variables like {user.firstName} should be parsed with fluid or not
     *
     * @var bool
     */
    protected $parseVariables = true;

    /**
     * ParseNewsletterUrlService constructor.
     * @param string $origin can be a page uid or a complete url
     */
    public function __construct(string $origin)
    {
        $url = '';
        if (MathUtility::canBeInterpretedAsInteger($origin)) {
            $urlSrervice = ObjectUtility::getObjectManager()->get(FrontendUrlService::class);
            $url = $urlSrervice->getTypolinkFromParameter($origin);
        } elseif (StringUtility::isValidUrl($origin)) {
            $url = $origin;
        }
        $this->url = $url;
    }

    /**
     * @param User|null $user
     * @return string
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    public function getParsedContent(User $user = null): string
    {
        if ($user === null) {
            $userFactory = ObjectUtility::getObjectManager()->get(UserFactory::class);
            $user = $userFactory->getDummyUser();
        }
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'BeforeParsing', [$user, $this]);
        $content = $this->getNewsletterContainer($this->getContentFromOrigin($user), $user);
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'AfterParsing', [$content, $this]);
        return $content;
    }

    /**
     * @param string $content
     * @param User $user
     * @return string
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function getNewsletterContainer(string $content, User $user): string
    {
        if ($this->isParseVariables()) {
            $standaloneView = ObjectUtility::getObjectManager()->get(StandaloneView::class);
            $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->containerFile));
            $standaloneView->assignMultiple(['content' => $content, 'user' => $user]);
            $html = $standaloneView->render();
        } else {
            $container = file_get_contents(GeneralUtility::getFileAbsFileName($this->containerFile));
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
        if ($this->isParseVariables()) {
            $parseService = ObjectUtility::getObjectManager()->get(ParseNewsletterService::class);
            $string = $parseService->parseBodytext($string, $user);
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
    public function isParseVariables(): bool
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
}

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
 * Class ParseNewsletterUrlService
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
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'BeforeParsing', [$user]);
        $content = $this->getNewsletterContainer($this->getContentFromOrigin($user), $user);
        $this->signalDispatch(__CLASS__, __FUNCTION__ . 'AfterParsing', [$content]);
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
        $standaloneView = ObjectUtility::getObjectManager()->get(StandaloneView::class);
        $standaloneView->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName($this->containerFile));
        $standaloneView->assignMultiple(['content' => $content, 'user' => $user]);
        $this->signalDispatch(__CLASS__, __FUNCTION__, [$standaloneView, $content, $user]);
        return $standaloneView->render();
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
        $standaloneView = ObjectUtility::getObjectManager()->get(StandaloneView::class);
        $standaloneView->setTemplateSource($string);
        $standaloneView->assignMultiple(['user' => $user]);
        $this->signalDispatch(__CLASS__, __FUNCTION__, [$standaloneView, $user]);
        return $standaloneView->render();
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
}

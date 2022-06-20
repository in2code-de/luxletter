<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Domain\Model;

use DateTime;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use In2code\Luxletter\Domain\Factory\UserFactory;
use In2code\Luxletter\Domain\Repository\LanguageRepository;
use In2code\Luxletter\Domain\Repository\LogRepository;
use In2code\Luxletter\Domain\Repository\QueueRepository;
use In2code\Luxletter\Domain\Service\Parsing\Newsletter as NewsletterParsing;
use In2code\Luxletter\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class User
 */
class Newsletter extends AbstractEntity
{
    const TABLE_NAME = 'tx_luxletter_domain_model_newsletter';

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @var bool
     */
    protected $disabled = false;

    /**
     * @var DateTime
     */
    protected $datetime = null;

    /**
     * @var string
     */
    protected $subject = '';

    /**
     * @var Usergroup
     */
    protected $receiver = null;

    /**
     * @var Configuration
     */
    protected $configuration = null;

    /**
     * @var string
     */
    protected $origin = '';

    /**
     * Contains container filename
     *
     * @var string
     */
    protected $layout = '';

    /**
     * @var string
     */
    protected $bodytext = '';

    /**
     * @var int|null
     */
    protected $dispatchedProgress = null;

    /**
     * @var int
     */
    protected $queues = 0;

    /**
     * @var int
     */
    protected $openers = 0;

    /**
     * @var int
     */
    protected $clickers = 0;

    /**
     * @var int
     */
    protected $unsubscribers = 0;

    /**
     * @var int
     */
    protected $language = 0;

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Newsletter
     */
    public function setTitle(string $title): Newsletter
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return Newsletter
     */
    public function setDescription(string $description): Newsletter
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return $this
     */
    public function enable()
    {
        $this->disabled = false;
        return $this;
    }

    /**
     * @return $this
     */
    public function disable()
    {
        $this->disabled = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->disabled === false;
    }

    /**
     * @return DateTime|null
     */
    public function getDatetime(): ?DateTime
    {
        return $this->datetime;
    }

    /**
     * @param DateTime $datetime
     * @return Newsletter
     */
    public function setDatetime(DateTime $datetime): Newsletter
    {
        $this->datetime = $datetime;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @return string
     * @throws InvalidConfigurationTypeException
     */
    public function getSubjectParsedWithDummyUser(): string
    {
        $userFactory = GeneralUtility::makeInstance(UserFactory::class);
        $user = $userFactory->getDummyUser();
        $newsletterParsing = GeneralUtility::makeInstance(NewsletterParsing::class);
        return $newsletterParsing->parseSubject($this->getSubject(), ['user' => $user]);
    }

    /**
     * @param string $subject
     * @return Newsletter
     */
    public function setSubject(string $subject): Newsletter
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return Usergroup
     */
    public function getReceiver(): ?Usergroup
    {
        return $this->receiver;
    }

    /**
     * @param Usergroup $receiver
     * @return Newsletter
     */
    public function setReceiver(Usergroup $receiver): self
    {
        $this->receiver = $receiver;
        return $this;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    /**
     * @param Configuration $configuration
     * @return Newsletter
     */
    public function setConfiguration(Configuration $configuration): self
    {
        $this->configuration = $configuration;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrigin(): string
    {
        return $this->origin;
    }

    /**
     * @param string $origin
     * @return Newsletter
     */
    public function setOrigin(string $origin): Newsletter
    {
        $this->origin = $origin;
        return $this;
    }

    /**
     * @return string
     */
    public function getLayout(): string
    {
        return $this->layout;
    }

    /**
     * @param string $layout
     * @return Newsletter
     */
    public function setLayout(string $layout): Newsletter
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @return string
     */
    public function getBodytext(): string
    {
        return $this->bodytext;
    }

    /**
     * @param string $bodytext
     * @return Newsletter
     */
    public function setBodytext(string $bodytext): Newsletter
    {
        $this->bodytext = $bodytext;
        return $this;
    }

    /**
     * @return int
     */
    public function getLanguage(): int
    {
        return $this->language;
    }

    /**
     * @return string
     * @throws ExceptionDbalDriver
     */
    public function getLanguageLabel(): string
    {
        $language = $this->getLanguage();
        if ($language > 0) {
            $languageRepository = GeneralUtility::makeInstance(LanguageRepository::class);
            return $languageRepository->getTitleFromIdentifier($language);
        }
        return LocalizationUtility::translateByKey('defaultLanguage');
    }

    /**
     * @param int $language
     * @return Newsletter
     */
    public function setLanguage(int $language): Newsletter
    {
        $this->language = $language;
        return $this;
    }

    /**
     * Checks the queue progress of this newsletter. 100 means 100% are sent.
     *
     * @return int
     */
    public function getDispatchProgress(): int
    {
        if ($this->dispatchedProgress === null) {
            $queueRepository = GeneralUtility::makeInstance(QueueRepository::class);
            $dispatched = $queueRepository->findAllByNewsletterAndDispatchedStatus($this, true)->count();
            $notDispatched = $queueRepository->findAllByNewsletterAndDispatchedStatus($this, false)->count();
            $overall = $dispatched + $notDispatched;
            $result = 0;
            if ($overall > 0) {
                $result = (int)(100 - ($notDispatched / $overall * 100));
            }
            $this->dispatchedProgress = $result;
        }
        return $this->dispatchedProgress;
    }

    /**
     * @return int
     */
    public function getQueues(): int
    {
        if ($this->queues === 0) {
            $queueRepository = GeneralUtility::makeInstance(QueueRepository::class);
            $queues = $queueRepository->findAllByNewsletter($this)->count();
            $this->queues = $queues;
        }
        return $this->queues;
    }

    /**
     * @return int
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function getOpeners(): int
    {
        if ($this->openers === 0) {
            $logRepository = GeneralUtility::makeInstance(LogRepository::class);
            $openers = count($logRepository->findByNewsletterAndStatus($this, Log::STATUS_NEWSLETTEROPENING));
            $this->openers = $openers;
        }
        return $this->openers;
    }

    /**
     * @return int
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function getClickers(): int
    {
        if ($this->clickers === 0) {
            $logRepository = GeneralUtility::makeInstance(LogRepository::class);
            $clickers = count($logRepository->findByNewsletterAndStatus($this, Log::STATUS_LINKOPENING));
            $this->clickers = $clickers;
        }
        return $this->clickers;
    }

    /**
     * @return int
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function getUnsubscribers(): int
    {
        if ($this->unsubscribers === 0) {
            $logRepository = GeneralUtility::makeInstance(LogRepository::class);
            $unsubscribers = count($logRepository->findByNewsletterAndStatus($this, Log::STATUS_UNSUBSCRIBE));
            $this->unsubscribers = $unsubscribers;
        }
        return $this->unsubscribers;
    }

    /**
     * @return float
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function getOpenRate(): float
    {
        $all = $this->getQueues();
        $openers = $this->getOpeners();
        if ($all > 0) {
            return $openers / $all;
        }
        return 0.0;
    }

    /**
     * @return float
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function getClickRate(): float
    {
        $openers = $this->getOpeners();
        $clickers = $this->getClickers();
        if ($openers > 0) {
            return $clickers / $openers;
        }
        return 0.0;
    }

    /**
     * @return float
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function getUnsubscribeRate(): float
    {
        $openers = $this->getOpeners();
        $unsubscribers = $this->getUnsubscribers();
        if ($openers > 0) {
            return $unsubscribers / $openers;
        }
        return 0.0;
    }
}

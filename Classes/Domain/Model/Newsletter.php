<?php

declare(strict_types=1);
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
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Newsletter extends AbstractEntity
{
    const TABLE_NAME = 'tx_luxletter_domain_model_newsletter';

    protected string $title = '';
    protected string $description = '';
    protected string $subject = '';
    protected string $origin = '';
    protected string $layout = '';
    protected string $bodytext = '';

    protected bool $disabled = false;

    protected int $queues = 0;
    protected int $openers = 0;
    protected int $clickers = 0;
    protected int $unsubscribers = 0;
    protected int $language = 0;
    protected ?int $dispatchedProgress = null;

    protected ?Category $category = null;
    protected ?DateTime $datetime = null;
    protected ?DateTime $crdate = null;
    protected ?Configuration $configuration = null;

    /**
     * @var ObjectStorage<Usergroup>
     */
    protected ObjectStorage $receivers;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): Newsletter
    {
        $this->title = $title;
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): Newsletter
    {
        $this->category = $category;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Newsletter
    {
        $this->description = $description;
        return $this;
    }

    public function enable(): self
    {
        $this->disabled = false;
        return $this;
    }

    public function disable(): self
    {
        $this->disabled = true;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->disabled === false;
    }

    public function getDatetime(): ?DateTime
    {
        return $this->datetime;
    }

    public function setDatetime(DateTime $datetime): self
    {
        $this->datetime = $datetime;
        return $this;
    }

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

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getReceivers(): ?ObjectStorage
    {
        return $this->receivers;
    }

    public function getReceiverGroupIdentifiers(): array
    {
        $receivers = $this->getReceivers();
        $identifiers = [];
        foreach ($receivers as $receiver) {
            $identifiers[] = $receiver->getUid();
        }
        return $identifiers;
    }

    public function setReceivers(ObjectStorage $receivers): self
    {
        $this->receivers = $receivers;
        return $this;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function setConfiguration(Configuration $configuration): self
    {
        $this->configuration = $configuration;
        return $this;
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function setOrigin(string $origin): self
    {
        $this->origin = $origin;
        return $this;
    }

    public function getLayout(): string
    {
        return $this->layout;
    }

    public function setLayout(string $layout): self
    {
        $this->layout = $layout;
        return $this;
    }

    public function getBodytext(): string
    {
        return $this->bodytext;
    }

    public function setBodytext(string $bodytext): self
    {
        $this->bodytext = $bodytext;
        return $this;
    }

    public function getLanguage(): int
    {
        return $this->language;
    }

    /**
     * Get a readable language label
     *
     * @return string
     * @throws ExceptionDbalDriver
     */
    public function getLanguageLabel(): string
    {
        $language = $this->getLanguage();
        if ($language > 0) {
            $languageRepository = GeneralUtility::makeInstance(LanguageRepository::class);
            return $languageRepository->getTitleFromIdentifier($language, $this->getOrigin());
        }
        return LocalizationUtility::translateByKey('defaultLanguage');
    }

    public function setLanguage(int $language): self
    {
        $this->language = $language;
        return $this;
    }

    public function getCrdate(): ?DateTime
    {
        return $this->crdate;
    }

    public function setCrdate(?DateTime $crdate): ?Newsletter
    {
        $this->crdate = $crdate;
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

    public function getQueues(): int
    {
        if ($this->queues === 0) {
            $queueRepository = GeneralUtility::makeInstance(QueueRepository::class);
            $queues = $queueRepository->findAllByNewsletter($this)->count();
            $this->queues = $queues;
        }
        return $this->queues;
    }

    public function getDispatchedQueues(): int
    {
        $queueRepository = GeneralUtility::makeInstance(QueueRepository::class);
        return $queueRepository->findAllByNewsletterAndDispatchedStatus($this, true)->count();
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
            $openers = count($logRepository->findByNewsletterAndStatus(
                $this,
                [Log::STATUS_NEWSLETTEROPENING, Log::STATUS_LINKOPENING]
            ));
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
            $clickers = count($logRepository->findByNewsletterAndStatus($this, [Log::STATUS_LINKOPENING]));
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
            $unsubscribers = count($logRepository->findByNewsletterAndStatus($this, [Log::STATUS_UNSUBSCRIBE], false));
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
        $dispatched = $this->getDispatchedQueues();
        $openers = $this->getOpeners();
        if ($dispatched > 0) {
            return $openers / $dispatched;
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

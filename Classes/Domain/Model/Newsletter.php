<?php
declare(strict_types=1);
namespace In2code\Luxletter\Domain\Model;

use Doctrine\DBAL\DBALException;
use In2code\Luxletter\Domain\Repository\LogRepository;
use In2code\Luxletter\Domain\Repository\QueueRepository;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\Exception;

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
     * @var \DateTime
     */
    protected $datetime = null;

    /**
     * @var string
     */
    protected $subject = '';

    /**
     * @var \In2code\Luxletter\Domain\Model\Usergroup
     */
    protected $receiver = null;

    /**
     * @var string
     */
    protected $origin = '';

    /**
     * @var string
     */
    protected $bodytext = '';

    /**
     * @var null|int
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
     * @return \DateTime
     */
    public function getDatetime(): ?\DateTime
    {
        return $this->datetime;
    }

    /**
     * @param \DateTime $datetime
     * @return Newsletter
     */
    public function setDatetime(\DateTime $datetime): Newsletter
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
     * Checks the queue progress of this newsletter. 100 means 100% are sent.
     *
     * @return int
     * @throws Exception
     */
    public function getDispatchProgress(): int
    {
        if ($this->dispatchedProgress === null) {
            $queueRepository = ObjectUtility::getObjectManager()->get(QueueRepository::class);
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
     * @throws Exception
     */
    public function getQueues(): int
    {
        if ($this->queues === 0) {
            $queueRepository = ObjectUtility::getObjectManager()->get(QueueRepository::class);
            $queues = $queueRepository->findAllByNewsletter($this)->count();
            $this->queues = $queues;
        }
        return $this->queues;
    }

    /**
     * @return int
     * @throws DBALException
     * @throws Exception
     */
    public function getOpeners(): int
    {
        if ($this->openers === 0) {
            /** @var $logRepository LogRepository */
            $logRepository = ObjectUtility::getObjectManager()->get(LogRepository::class);
            $openers = count($logRepository->findByNewsletterAndStatus($this, [Log::STATUS_NEWSLETTEROPENING, Log::STATUS_LINKOPENING], true));
            $this->openers = $openers;
        }
        return $this->openers;
    }

    /**
     * @return int
     * @throws DBALException
     * @throws Exception
     */
    public function getClickers(): int
    {
        if ($this->clickers === 0) {
            /** @var $logRepository LogRepository */
            $logRepository = ObjectUtility::getObjectManager()->get(LogRepository::class);
            $clickers = count($logRepository->findByNewsletterAndStatus($this, Log::STATUS_LINKOPENING, true));
            $this->clickers = $clickers;
        }
        return $this->clickers;
    }

    /**
     * @return int
     * @throws DBALException
     * @throws Exception
     */
    public function getUnsubscribers(): int
    {
        if ($this->unsubscribers === 0) {
            $logRepository = ObjectUtility::getObjectManager()->get(LogRepository::class);
            $unsubscribers = count($logRepository->findByNewsletterAndStatus($this, Log::STATUS_UNSUBSCRIBE));
            $this->unsubscribers = $unsubscribers;
        }
        return $this->unsubscribers;
    }

    /**
     * @return float
     * @throws DBALException
     * @throws Exception
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
     * @throws Exception
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
     * @throws Exception
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

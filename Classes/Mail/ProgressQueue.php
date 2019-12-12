<?php
declare(strict_types=1);
namespace In2code\Luxletter\Mail;

use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Domain\Repository\Configuration\ConfigurationRepository;
use In2code\Luxletter\Domain\Repository\QueueRepository;
use In2code\Luxletter\Domain\Service\LinkHashingService;
use In2code\Luxletter\Domain\Service\LogService;
use In2code\Luxletter\Domain\Service\ParseNewsletterService;
use In2code\Luxletter\Signal\SignalTrait;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class ProgressQueue
 */
class ProgressQueue
{
    use SignalTrait;

    /**
     * @var QueueRepository
     */
    protected $queueRepository = null;

    /**
     * @var ConfigurationRepository
     */
    protected $configurationRepository;

    /**
     * ProgressQueue constructor.
     */
    public function __construct()
    {
        $this->queueRepository = ObjectUtility::getObjectManager()->get(QueueRepository::class);
        $this->configurationRepository = ObjectUtility::getObjectManager()->get(ConfigurationRepository::class);
    }

    /**
     * @param int $limit
     * @return int Number of progressed queued mails
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws InvalidQueryException
     * @throws InvalidConfigurationTypeException
     * @throws UnknownObjectException
     */
    public function progress(int $limit = 50): int
    {
        $queues = $this->queueRepository->findDispatchableInQueue($limit);
        $this->signalDispatch(__CLASS__, __FUNCTION__, [$queues]);
        foreach ($queues as $queue) {
            /** @var Queue $queue */
            $this->sendNewsletterToReceiverInQueue($queue);
            $this->markSent($queue);
        }
        return $queues->count();
    }

    /**
     * @param Queue $queue
     * @return void
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws InvalidConfigurationTypeException
     * @throws \Exception
     */
    protected function sendNewsletterToReceiverInQueue(Queue $queue): void
    {
        $parseService = ObjectUtility::getObjectManager()->get(ParseNewsletterService::class);
        $bodytext = $parseService->parseMailText(
            $queue->getNewsletter()->getBodytext(),
            ['user' => $queue->getUser(), 'newsletter' => $queue->getNewsletter()]
        );
        $bodytext = $this->hashLinksInBodytext($queue, $bodytext);
        $configuration = $this->configurationRepository->findOneByIdentifier($queue->getNewsletter()->getConfigurationId());
        $sendMail = ObjectUtility::getObjectManager()->get(
            SendMail::class,
            $parseService->parseMailText(
                $queue->getNewsletter()->getSubject(),
                ['user' => $queue->getUser(), 'newsletter' => $queue->getNewsletter()]
            ),
            $bodytext,
            $configuration->getFromEmail(),
            $configuration->getFromName(),
            $configuration->getReplyEmail(),
            $configuration->getReplyName()
        );
        $sendMail->sendNewsletter($queue->getEmail());
        $logService = ObjectUtility::getObjectManager()->get(LogService::class);
        $logService->logNewsletterDispatch($queue->getNewsletter(), $queue->getUser());
    }

    /**
     * @param Queue $queue
     * @param string $bodytext
     * @return string
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws \Exception
     */
    protected function hashLinksInBodytext(Queue $queue, string $bodytext): string
    {
        $configuration = $this->configurationRepository->findOneByIdentifier($queue->getNewsletter()->getConfigurationId());
        if ($configuration->isRewriteLinksInNewsletterActivated()) {
            $linkHashing = ObjectUtility::getObjectManager()->get(
                LinkHashingService::class,
                $queue->getNewsletter(),
                $queue->getUser()
            );
            $bodytext = $linkHashing->hashLinks($bodytext);
        }
        return $bodytext;
    }

    /**
     * @param Queue $queue
     * @return void
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    protected function markSent(Queue $queue)
    {
        $queue->setSent();
        $this->queueRepository->update($queue);
        $this->queueRepository->persistAll();
    }
}

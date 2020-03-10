<?php
declare(strict_types=1);
namespace In2code\Luxletter\Mail;

use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Domain\Repository\QueueRepository;
use In2code\Luxletter\Domain\Service\LinkHashingService;
use In2code\Luxletter\Domain\Service\LogService;
use In2code\Luxletter\Domain\Service\ParseNewsletterService;
use In2code\Luxletter\Exception\ArgumentMissingException;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Signal\SignalTrait;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\Exception;
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
     * ProgressQueue constructor.
     */
    public function __construct()
    {
        $this->queueRepository = ObjectUtility::getObjectManager()->get(QueueRepository::class);
    }

    /**
     * @param int $limit
     * @return int Number of progressed queued mails
     * @throws ArgumentMissingException
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
     * @throws InvalidQueryException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws MisconfigurationException
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
     * @throws ArgumentMissingException
     * @throws Exception
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws MisconfigurationException
     */
    protected function sendNewsletterToReceiverInQueue(Queue $queue): void
    {
        $parseService = ObjectUtility::getObjectManager()->get(ParseNewsletterService::class);
        $bodytext = $parseService->parseMailText(
            $queue->getNewsletter()->getBodytext(),
            ['user' => $queue->getUser(), 'newsletter' => $queue->getNewsletter()]
        );
        $bodytext = $this->hashLinksInBodytext($queue, $bodytext);
        $sendMail = ObjectUtility::getObjectManager()->get(
            SendMail::class,
            $parseService->parseMailText(
                $queue->getNewsletter()->getSubject(),
                ['user' => $queue->getUser(), 'newsletter' => $queue->getNewsletter()]
            ),
            $bodytext
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
     * @throws ArgumentMissingException
     * @throws MisconfigurationException
     * @throws Exception
     */
    protected function hashLinksInBodytext(Queue $queue, string $bodytext): string
    {
        if (ConfigurationUtility::isRewriteLinksInNewsletterActivated()) {
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

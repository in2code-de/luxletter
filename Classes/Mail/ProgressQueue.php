<?php
declare(strict_types=1);
namespace In2code\Luxletter\Mail;

use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Domain\Repository\QueueRepository;
use In2code\Luxletter\Domain\Service\LinkHashingService;
use In2code\Luxletter\Domain\Service\LogService;
use In2code\Luxletter\Domain\Service\ParseNewsletterService;
use In2code\Luxletter\Utility\ConfigurationUtility;
use In2code\Luxletter\Utility\ObjectUtility;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class ProgressQueue
 */
class ProgressQueue
{

    /**
     * @param int $limit
     * @return int Number of progressed queued mails
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws InvalidQueryException
     */
    public function progress(int $limit = 50): int
    {
        $queueRepository = ObjectUtility::getObjectManager()->get(QueueRepository::class);
        $queues = $queueRepository->findDispatchableInQueue($limit);
        foreach ($queues as $queue) {
            /** @var Queue $queue */
            $this->sendNewsletterToReceiverInQueue($queue);
            $queueRepository->delete($queue);
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
     */
    protected function sendNewsletterToReceiverInQueue(Queue $queue): void
    {
        $parseService = ObjectUtility::getObjectManager()->get(ParseNewsletterService::class);
        $bodytext = $parseService->parseMailText($queue->getNewsletter()->getBodytext(), $queue->getUser());
        $bodytext = $this->hashLinksInBodytext($queue, $bodytext);
        $sendMail = ObjectUtility::getObjectManager()->get(
            SendMail::class,
            $parseService->parseMailText($queue->getNewsletter()->getSubject(), $queue->getUser()),
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
}

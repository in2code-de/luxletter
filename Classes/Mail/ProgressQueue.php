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
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
     * @var ParseNewsletterService
     */
    protected $parseService = null;

    /**
     * @var OutputInterface
     */
    protected $output = null;

    /**
     * ProgressQueue constructor.
     * @noinspection PhpUnhandledExceptionInspection
     * @param OutputInterface $output
     * @throws Exception
     */
    public function __construct(OutputInterface $output)
    {
        $this->queueRepository = ObjectUtility::getObjectManager()->get(QueueRepository::class);
        $this->parseService = ObjectUtility::getObjectManager()->get(ParseNewsletterService::class);
        $this->output = $output;
    }

    /**
     * @param int $limit
     * @param int $newsletterIdentifier
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
     * @throws TransportExceptionInterface
     * @throws UnknownObjectException
     */
    public function progress(int $limit, int $newsletterIdentifier): int
    {
        $queues = $this->queueRepository->findDispatchableInQueue($limit, $newsletterIdentifier);
        if ($queues->count() > 0) {
            $progress = new ProgressBar($this->output, $queues->count());
            $progress->start();
            $this->signalDispatch(__CLASS__, __FUNCTION__, [$queues]);
            foreach ($queues as $queue) {
                /** @var Queue $queue */
                $this->sendNewsletterToReceiverInQueue($queue);
                $this->markSent($queue);
                $progress->advance();
            }
            $this->output->writeln('');
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
     * @throws TransportExceptionInterface
     */
    protected function sendNewsletterToReceiverInQueue(Queue $queue): void
    {
        if ($queue->getUser() !== null) {
            /** @var SendMail $sendMail */
            $sendMail = ObjectUtility::getObjectManager()->get(
                SendMail::class,
                $this->getSubject($queue),
                $this->getBodyText($queue),
                $queue->getNewsletter()->getConfiguration()
            );
            $sendMail->sendNewsletter([$queue->getEmail() => $queue->getUser()->getReadableName()]);
            $logService = ObjectUtility::getObjectManager()->get(LogService::class);
            $logService->logNewsletterDispatch($queue->getNewsletter(), $queue->getUser());
        }
    }

    /**
     * @param Queue $queue
     * @return string
     * @throws Exception
     * @throws InvalidConfigurationTypeException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function getSubject(Queue $queue): string
    {
        return $this->parseService->parseMailText(
            $queue->getNewsletter()->getSubject(),
            [
                'user' => $queue->getUser(),
                'newsletter' => $queue->getNewsletter(),
                'site' => $queue->getNewsletter()->getConfiguration()->getSiteConfiguration()
            ]
        );
    }

    /**
     * @param Queue $queue
     * @return string
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
    protected function getBodyText(Queue $queue): string
    {
        $bodytext = $this->parseService->parseMailText(
            $queue->getNewsletter()->getBodytext(),
            [
                'user' => $queue->getUser(),
                'newsletter' => $queue->getNewsletter(),
                'site' => $queue->getNewsletter()->getConfiguration()->getSiteConfiguration()
            ]
        );
        $bodytext = $this->hashLinksInBodytext($queue, $bodytext);
        return $bodytext;
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
            /** @var LinkHashingService $linkHashing */
            $linkHashing = GeneralUtility::makeInstance(
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
     * @throws Exception
     */
    protected function markSent(Queue $queue)
    {
        $queue->setSent();
        $this->queueRepository->update($queue);
        $this->queueRepository->persistAll();
    }
}

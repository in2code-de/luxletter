<?php
declare(strict_types=1);
namespace In2code\Luxletter\Command;

use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Domain\Repository\QueueRepository;
use In2code\Luxletter\Domain\Service\ParseNewsletterService;
use In2code\Luxletter\Mail\SendMail;
use In2code\Luxletter\Utility\ObjectUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class QueueCommand
 */
class QueueCommand extends Command {

    /**
     * Configure the command by defining the name, options and arguments
     */
    public function configure()
    {
        $this->setDescription('Send a bunch of emails from the queue.');
        $this->addArgument('amount', InputArgument::OPTIONAL, 'How many mails should be send per wave?', 50);
    }

    /**
     * Sends a bunch of emails from the queue
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws InvalidQueryException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queueRepository = ObjectUtility::getObjectManager()->get(QueueRepository::class);
        $queues = $queueRepository->findDispatchableInQueue((int)$input->getArgument('amount'));
        foreach ($queues as $queue) {
            /** @var Queue $queue */
            $this->sendNewsletterToReceiverInQueue($queue);
            $queueRepository->delete($queue);
        }
        $output->writeln('Just sent ' . $queues->count() . ' emails from the queue...');
        return 0;
    }

    /**
     * @param Queue $queue
     * @return void
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     */
    protected function sendNewsletterToReceiverInQueue(Queue $queue): void
    {
        $parseService = ObjectUtility::getObjectManager()->get(ParseNewsletterService::class);
        $sendMail = ObjectUtility::getObjectManager()->get(
            SendMail::class,
            $parseService->parseBodytext($queue->getNewsletter()->getSubject(), $queue->getUser()),
            $parseService->parseBodytext($queue->getNewsletter()->getBodytext(), $queue->getUser())
        );
        $sendMail->sendNewsletter($queue->getEmail());
    }
}

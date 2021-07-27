<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Command;

use In2code\Luxletter\Domain\Repository\QueueRepository;
use In2code\Luxletter\Utility\ObjectUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class ClearQueueCommand
 */
class ClearQueueCommand extends Command
{
    /**
     * Configure the command by defining the name, options and arguments
     */
    public function configure()
    {
        $this->setDescription('Remove all queued newsletters');
    }

    /**
     * Sends a bunch of emails from the queue
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var QueueRepository $queueRepository */
        $queueRepository = ObjectUtility::getObjectManager()->get(QueueRepository::class);
        $queueRepository->truncate();
        $output->writeln('Truncated queue table!');
        return 0;
    }
}

<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Command;

use In2code\Luxletter\Domain\Repository\QueueRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queueRepository = GeneralUtility::makeInstance(QueueRepository::class);
        $queueRepository->truncate();
        $output->writeln('Truncated queue table!');
        return self::SUCCESS;
    }
}

<?php

declare(strict_types=1);
namespace In2code\Luxletter\Command;

use In2code\Luxletter\Domain\Repository\QueueRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ClearQueueCommand extends Command
{
    public function configure()
    {
        $this->setDescription('Remove all queued newsletters');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queueRepository = GeneralUtility::makeInstance(QueueRepository::class);
        $queueRepository->truncate();
        $output->writeln('Truncated queue table!');
        return parent::SUCCESS;
    }
}

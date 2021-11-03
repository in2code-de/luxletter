<?php
declare(strict_types = 1);
namespace In2code\Luxletter\Command;

use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ClearCommand
 */
class ClearCommand extends Command
{
    /**
     * Configure the command by defining the name, options and arguments
     */
    public function configure()
    {
        $this->setDescription('Remove all data of (newsletter, log, queue, link) luxletter!!!');
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
        $newsletterRepository = GeneralUtility::makeInstance(NewsletterRepository::class);
        $newsletterRepository->truncateAll();
        $output->writeln('Truncated all luxletter tables!');
        return 0;
    }
}

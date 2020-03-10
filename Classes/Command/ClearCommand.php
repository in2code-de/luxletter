<?php
declare(strict_types=1);
namespace In2code\Luxletter\Command;

use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Utility\ObjectUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class ClearCommand
 */
class ClearCommand extends Command {

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
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $newsletterRepository = ObjectUtility::getObjectManager()->get(NewsletterRepository::class);
        $newsletterRepository->truncateAll();
        $output->writeln('Truncated all luxletter tables!');
        return 0;
    }
}

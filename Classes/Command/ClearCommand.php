<?php

declare(strict_types=1);
namespace In2code\Luxletter\Command;

use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ClearCommand extends Command
{
    public function configure()
    {
        $this->setDescription('Be careful!!! Removes all data of (newsletter, log, queue, link) luxletter.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $newsletterRepository = GeneralUtility::makeInstance(NewsletterRepository::class);
        $newsletterRepository->truncateAll();
        $output->writeln('Truncated all luxletter tables!');
        return parent::SUCCESS;
    }
}

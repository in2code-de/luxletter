<?php

declare(strict_types=1);
namespace In2code\Luxletter\Command;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Exception;
use In2code\Luxletter\Domain\Factory\NewsletterFactory;
use In2code\Luxletter\Domain\Service\QueueService;
use In2code\Luxletter\Exception\InvalidUrlException;
use In2code\Luxletter\Exception\MisconfigurationException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Exception\SiteNotFoundException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;

/**
 * CreateNewsletterFromOriginCommand
 */
class CreateNewsletterFromOriginCommand extends Command
{
    /**
     * Configure the command by defining the name, options and arguments
     */
    public function configure()
    {
        $this->setDescription('Create a newsletter from CLI or Scheduler');
        $this->addArgument('title', InputArgument::REQUIRED, 'Newsletter title');
        $this->addArgument('usergroup', InputArgument::REQUIRED, 'fe_groups.uid as receiver group');
        $this->addArgument('configuration', InputArgument::REQUIRED, 'Sender configuration uid');
        $this->addArgument('origin', InputArgument::REQUIRED, 'Page identifier or absolute URL');
        $this->addArgument('language', InputArgument::OPTIONAL, 'Language for newsletter', 0);
        $this->addArgument('layout', InputArgument::OPTIONAL, 'Layout template name', 'NewsletterContainer');
        $this->addArgument('subject', InputArgument::OPTIONAL, 'Newsletter subject', '');
        $this->addArgument('description', InputArgument::OPTIONAL, 'Newsletter description', '');
        $this->addArgument('date', InputArgument::OPTIONAL, 'Newsletter date in format "Y-m-d\TH:i"', '');
    }

    /**
     * Sends a bunch of emails from the queue
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws IllegalObjectTypeException
     * @throws InvalidUrlException
     * @throws MisconfigurationException
     * @throws InvalidConfigurationTypeException
     * @throws SiteNotFoundException
     * @throws Exception
     * @throws ExceptionDbalDriver
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $newsletterFactory = GeneralUtility::makeInstance(NewsletterFactory::class);
        $newsletter = $newsletterFactory->get(
            $input->getArgument('title'),
            (int)$input->getArgument('usergroup'),
            (int)$input->getArgument('configuration'),
            $input->getArgument('origin'),
            (int)$input->getArgument('language'),
            $input->getArgument('layout'),
            $input->getArgument('description'),
            $input->getArgument('date'),
            $input->getArgument('subject')
        );
        $output->writeln('Newsletter with uid ' . $newsletter->getUid() . ' created');

        $queueService = GeneralUtility::makeInstance(QueueService::class);
        $queuedAmount = $queueService->addMailReceiversToQueue($newsletter, (int)$input->getArgument('language'));
        $output->writeln('Added ' . $queuedAmount . ' queue records');
        return 0;
    }
}

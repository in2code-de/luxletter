<?php

declare(strict_types=1);
namespace In2code\Luxletter\Command;

use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Domain\Service\QueueService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BuildQueueCommand extends Command
{
    public function configure()
    {
        $this->setDescription('Build a queue to a newsletter. Normally used together with "asynchronousQueueStorage"');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $newsletterRepository = GeneralUtility::makeInstance(NewsletterRepository::class);
        $newsletter = $newsletterRepository->findOneNotQueued();

        if ($newsletter !== null) {
            $newsletterRepository->update($newsletter);
            $newsletterRepository->persistAll();

            $queueService = GeneralUtility::makeInstance(QueueService::class);
            $queuedCount = $queueService->addMailReceiversToQueue($newsletter, $newsletter->getLanguage());
            $output->writeln('Added ' . $queuedCount . ' queue records for newsletter with UID ' . $newsletter->getUid());
        } else {
            $output->writeln('No newsletters for new queues found');
        }

        return parent::SUCCESS;
    }
}

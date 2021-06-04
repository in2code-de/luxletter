<?php
declare(strict_types=1);
namespace In2code\Luxletter\Command;

use In2code\Luxletter\Exception\ArgumentMissingException;
use In2code\Luxletter\Exception\MisconfigurationException;
use In2code\Luxletter\Mail\ProgressQueue;
use In2code\Luxletter\Utility\ObjectUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotException;
use TYPO3\CMS\Extbase\SignalSlot\Exception\InvalidSlotReturnException;

/**
 * Class QueueCommand
 */
class QueueCommand extends Command
{

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
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws IllegalObjectTypeException
     * @throws InvalidConfigurationTypeException
     * @throws InvalidQueryException
     * @throws InvalidSlotException
     * @throws InvalidSlotReturnException
     * @throws UnknownObjectException
     * @throws ArgumentMissingException
     * @throws MisconfigurationException
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $progressQueue = ObjectUtility::getObjectManager()->get(ProgressQueue::class, $output);
        $progressed = $progressQueue->progress((int)$input->getArgument('amount'));
        if ($progressed > 0) {
            $output->writeln('Successfully sent ' . $progressed . ' email(s) from the queue...');
        } else {
            $output->writeln('No mails in queue found');
        }
        return 0;
    }
}

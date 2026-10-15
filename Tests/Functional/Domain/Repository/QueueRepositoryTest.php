<?php

namespace In2code\Luxletter\Tests\Functional\Domain\Repository;

use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Domain\Repository\QueueRepository;
use In2code\Luxletter\Domain\Repository\UserRepository;
use In2code\Luxletter\Tests\Functional\AbstractFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

#[CoversClass(QueueRepository::class)]
class QueueRepositoryTest extends AbstractFunctionalTestCase
{
    protected QueueRepository $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/Receivers.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/Newsletters.csv');
        $this->importCSVDataSet(__DIR__ . '/Fixtures/Queue.csv');
        $this->subject = $this->get(QueueRepository::class);
    }

    /**
     * Skipped are: future datetime, already sent, too many failures, disabled newsletter, newsletter without
     * configuration or layout, disabled or deleted user
     *
     * @return void
     */
    public function testFindDispatchableInQueueReturnsOnlyDispatchableEntriesOrderedByTstamp(): void
    {
        self::assertSame([10, 1], $this->getUids($this->subject->findDispatchableInQueue(100, 0)));
    }

    /**
     * @return void
     */
    public function testFindDispatchableInQueueRespectsLimit(): void
    {
        self::assertSame([10], $this->getUids($this->subject->findDispatchableInQueue(1, 0)));
    }

    /**
     * @return void
     */
    public function testFindDispatchableInQueueFiltersByNewsletter(): void
    {
        self::assertSame([10, 1], $this->getUids($this->subject->findDispatchableInQueue(100, 1)));
        self::assertSame([], $this->getUids($this->subject->findDispatchableInQueue(100, 2)));
    }

    /**
     * @return void
     */
    public function testIsUserAndNewsletterAlreadyAddedToQueue(): void
    {
        $userRepository = $this->get(UserRepository::class);
        $queuedUser = $userRepository->findByUid(1);
        $notQueuedUser = $userRepository->findByUid(3);
        $newsletter = $this->get(NewsletterRepository::class)->findByUid(1);

        self::assertTrue($this->subject->isUserAndNewsletterAlreadyAddedToQueue($queuedUser, $newsletter));
        self::assertFalse($this->subject->isUserAndNewsletterAlreadyAddedToQueue($notQueuedUser, $newsletter));
    }

    protected function getUids(QueryResultInterface $queues): array
    {
        return array_map(static fn (Queue $queue): int => $queue->getUid(), $queues->toArray());
    }
}

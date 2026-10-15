<?php

namespace In2code\Luxletter\Tests\Functional\Domain\Service;

use In2code\Luxletter\Domain\Model\Newsletter;
use In2code\Luxletter\Domain\Model\Queue;
use In2code\Luxletter\Domain\Repository\NewsletterRepository;
use In2code\Luxletter\Domain\Service\QueueService;
use In2code\Luxletter\Exception\RecordInDatabaseNotFoundException;
use In2code\Luxletter\Tests\Functional\AbstractFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(QueueService::class)]
class QueueServiceTest extends AbstractFunctionalTestCase
{
    protected QueueService $subject;

    public function setUp(): void
    {
        parent::setUp();
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/Receivers.csv');
        $this->importCSVDataSet(__DIR__ . '/../../Fixtures/Newsletters.csv');
        $this->subject = $this->get(QueueService::class);
    }

    /**
     * @return void
     */
    public function testAddMailReceiversToQueueAddsOneEntryPerReceiverAndMarksNewsletterAsQueued(): void
    {
        $amount = $this->subject->addMailReceiversToQueue($this->getNewsletter(1), -1);

        self::assertSame(3, $amount);
        self::assertSame(['alice@example.org', 'bob@example.org', 'dave@example.org'], $this->getQueuedEmails());
        self::assertSame(1, $this->getColumn(Newsletter::TABLE_NAME, 'queued')[0]);
    }

    /**
     * @return void
     */
    public function testAddMailReceiversToQueueRespectsLanguage(): void
    {
        $this->subject->addMailReceiversToQueue($this->getNewsletter(1), 1);

        self::assertSame(['bob@example.org', 'dave@example.org'], $this->getQueuedEmails());
    }

    /**
     * @return void
     */
    public function testAddMailReceiversToQueueDoesNotAddReceiversTwice(): void
    {
        $this->subject->addMailReceiversToQueue($this->getNewsletter(1), -1);
        $this->subject->addMailReceiversToQueue($this->getNewsletter(1), -1);

        self::assertSame(['alice@example.org', 'bob@example.org', 'dave@example.org'], $this->getQueuedEmails());
    }

    /**
     * @return void
     */
    public function testAddUserWithNewsletterToQueueAddsUserWithValidEmail(): void
    {
        $this->subject->addUserWithNewsletterToQueue(3, 1);

        self::assertSame(['carol@example.org'], $this->getQueuedEmails());
    }

    /**
     * @return void
     */
    public function testAddUserWithNewsletterToQueueSkipsUserWithInvalidEmail(): void
    {
        $this->subject->addUserWithNewsletterToQueue(5, 1);

        self::assertSame([], $this->getQueuedEmails());
    }

    /**
     * @return void
     */
    public function testAddUserWithNewsletterToQueueThrowsExceptionForUnknownUser(): void
    {
        $this->expectException(RecordInDatabaseNotFoundException::class);
        $this->expectExceptionCode(1585479415);
        $this->subject->addUserWithNewsletterToQueue(99, 1);
    }

    /**
     * @return void
     */
    public function testAddUserWithNewsletterToQueueThrowsExceptionForUnknownNewsletter(): void
    {
        $this->expectException(RecordInDatabaseNotFoundException::class);
        $this->expectExceptionCode(1585479403);
        $this->subject->addUserWithNewsletterToQueue(1, 99);
    }

    /**
     * @return void
     */
    public function testAddUserWithLatestNewsletterToQueueUsesNewestNewsletter(): void
    {
        $this->subject->addUserWithLatestNewsletterToQueue(3);

        self::assertSame([[4, 'carol@example.org']], $this->getRows(Queue::TABLE_NAME, ['newsletter', 'email']));
    }

    /**
     * @return void
     */
    public function testAddUserWithLatestNewsletterToQueueThrowsExceptionForUnknownUser(): void
    {
        $this->expectException(RecordInDatabaseNotFoundException::class);
        $this->expectExceptionCode(1585479087);
        $this->subject->addUserWithLatestNewsletterToQueue(99);
    }

    protected function getNewsletter(int $identifier): Newsletter
    {
        return $this->get(NewsletterRepository::class)->findByUid($identifier);
    }

    protected function getQueuedEmails(): array
    {
        return $this->getColumn(Queue::TABLE_NAME, 'email', 'email');
    }
}

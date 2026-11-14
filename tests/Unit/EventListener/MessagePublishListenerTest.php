<?php

declare(strict_types=1);

namespace Wexample\SymfonyConversation\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\Exception\RuntimeException as MercureRuntimeException;
use Wexample\SymfonyConversation\Api\Normalizer\Entity\ConversationMessage\DefaultMessageNormalizer;
use Wexample\SymfonyConversation\Entity\Conversation;
use Wexample\SymfonyConversation\Entity\ConversationMessage;
use Wexample\SymfonyConversation\Entity\UserConversationParticipant;
use Wexample\SymfonyConversation\EventListener\MessagePublishListener;
use Wexample\SymfonyConversation\Repository\MessageRepository;
use Wexample\SymfonyConversation\Repository\ParticipantRepository;
use Wexample\SymfonyConversation\Service\ConversationUnreadService;
use Wexample\SymfonyConversation\Tests\Unit\TestUser;
use Wexample\SymfonyLive\Service\LivePublisherService;

class MessagePublishListenerTest extends TestCase
{
    private function persist(
        MessagePublishListener $listener,
        ConversationMessage ...$messages
    ): void {
        $entityManager = $this->createStub(EntityManagerInterface::class);

        foreach ($messages as $message) {
            $listener->postPersist(new PostPersistEventArgs($message, $entityManager));
        }

        $listener->postFlush(new PostFlushEventArgs($entityManager));
    }

    public function testParticipantsAreAskedOncePerConversationAndReadersToldOnce(): void
    {
        $conversation = new Conversation();
        $other = new Conversation();
        $alice = new UserConversationParticipant($conversation, new TestUser());
        $bob = new UserConversationParticipant($conversation, new TestUser());

        $participants = $this->createMock(ParticipantRepository::class);
        $participants->expects($this->exactly(2))
            ->method('findByConversation')
            ->willReturnCallback(fn (Conversation $asked): array => $asked === $conversation ? [$alice, $bob] : []);

        $events = [];
        $publisher = $this->createStub(LivePublisherService::class);
        $publisher->method('publishEvent')->willReturnCallback(function (string|array $topic, string $event) use (&$events): string {
            $events[] = $event;

            return 'id';
        });

        $listener = new MessagePublishListener(
            $publisher,
            $this->createStub(DefaultMessageNormalizer::class),
            $participants,
            new ConversationUnreadService($this->createStub(MessageRepository::class), $publisher),
        );

        $this->persist(
            $listener,
            (new ConversationMessage($conversation))->setAuthor($alice),
            (new ConversationMessage($conversation))->setAuthor($alice),
            new ConversationMessage($other),
        );

        $this->assertSame([
            MessagePublishListener::EVENT_MESSAGE_CREATED,
            MessagePublishListener::EVENT_MESSAGE_CREATED,
            MessagePublishListener::EVENT_MESSAGE_CREATED,
            // Bob alone, who did not write the lines, and once.
            ConversationUnreadService::EVENT_UNREAD,
        ], $events);
    }

    public function testAnUnreachableHubIsLoggedAndTheNextLinesStillAnnounced(): void
    {
        $conversation = new Conversation();

        $participants = $this->createStub(ParticipantRepository::class);
        $participants->method('findByConversation')->willReturn([]);

        $calls = 0;
        $publisher = $this->createStub(LivePublisherService::class);
        $publisher->method('publishEvent')->willReturnCallback(function () use (&$calls): string {
            if (1 === ++$calls) {
                throw new MercureRuntimeException('hub down');
            }

            return 'id';
        });

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('warning');

        $listener = new MessagePublishListener(
            $publisher,
            $this->createStub(DefaultMessageNormalizer::class),
            $participants,
            new ConversationUnreadService($this->createStub(MessageRepository::class), $publisher),
            $logger,
        );

        $this->persist($listener, new ConversationMessage($conversation), new ConversationMessage($conversation));

        $this->assertSame(2, $calls);
    }
}

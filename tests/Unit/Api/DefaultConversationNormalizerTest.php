<?php

declare(strict_types=1);

namespace Wexample\SymfonyConversation\Tests\Unit\Api;

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;
use Wexample\SymfonyConversation\Api\Normalizer\Entity\Conversation\DefaultConversationNormalizer;
use Wexample\SymfonyConversation\Api\Normalizer\Entity\ConversationParticipant\DefaultParticipantNormalizer;
use Wexample\SymfonyConversation\Entity\Conversation;
use Wexample\SymfonyConversation\Entity\UserConversationParticipant;
use Wexample\SymfonyConversation\Repository\MessageRepository;
use Wexample\SymfonyConversation\Tests\Unit\TestUser;

class DefaultConversationNormalizerTest extends TestCase
{
    private function normalizer(
        ?TestUser $reader,
        MessageRepository $messages
    ): DefaultConversationNormalizer {
        $security = $this->createStub(Security::class);
        $security->method('getUser')->willReturn($reader);

        $participantNormalizer = $this->createStub(DefaultParticipantNormalizer::class);
        $participantNormalizer->method('flatten')->willReturn([]);

        return new DefaultConversationNormalizer($participantNormalizer, $messages, $security);
    }

    public function testTheReadersOwnParticipantIsFoundAmongTheLoadedOnes(): void
    {
        $reader = new TestUser();
        $conversation = new Conversation();
        $conversation->addParticipant(new UserConversationParticipant($conversation, new TestUser()));
        $conversation->addParticipant($mine = new UserConversationParticipant($conversation, $reader));

        $messages = $this->createMock(MessageRepository::class);
        $messages->expects($this->once())->method('countUnreadFor')->with($mine)->willReturn(3);

        $dto = $this->normalizer($reader, $messages)->normalizeEntity($conversation);

        $this->assertSame((string) $mine->getId(), $dto->me);
        $this->assertSame(3, $dto->unread);
        $this->assertCount(2, $dto->participants);
    }

    public function testAReaderTakingNoPartHasNoParticipantNorUnread(): void
    {
        $conversation = new Conversation();
        $conversation->addParticipant(new UserConversationParticipant($conversation, new TestUser()));

        $messages = $this->createMock(MessageRepository::class);
        $messages->expects($this->never())->method('countUnreadFor');

        $dto = $this->normalizer(new TestUser(), $messages)->normalizeEntity($conversation);

        $this->assertNull($dto->me);
    }
}

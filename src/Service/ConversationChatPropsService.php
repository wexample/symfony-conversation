<?php

namespace Wexample\SymfonyConversation\Service;

use Wexample\SymfonyConversation\Api\Normalizer\Entity\ConversationParticipant\DefaultParticipantNormalizer;
use Wexample\SymfonyConversation\Entity\Conversation;
use Wexample\SymfonyConversation\Repository\UserParticipantRepository;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;

/**
 * One conversation as a chat takes it, whoever is on the other side — people,
 * an agent among them (ConversationChatMixin): who takes part, who the reader
 * is among them, what they last read — read before the chat marks it.
 */
final readonly class ConversationChatPropsService
{
    public function __construct(
        private UserParticipantRepository $userParticipants,
        private DefaultParticipantNormalizer $participantNormalizer,
    ) {
    }

    /**
     * Nobody signed in — an application without accounts —, the chat has no
     * reader among the people: every line is someone else's.
     *
     * @return array{conversationId: string, me: ?string, people: list<array>, unreadSince: ?string}
     */
    public function props(Conversation $conversation, ?UserEntityInterface $user): array
    {
        $me = $user ? $this->userParticipants->findOneByConversationAndUser($conversation, $user) : null;

        return [
            'conversationId' => (string) $conversation->getId(),
            'me' => $me ? (string) $me->getId() : null,
            'people' => array_values(array_map(
                fn ($participant) => $this->participantNormalizer->flatten($participant),
                $conversation->getParticipants()->toArray()
            )),
            'unreadSince' => $me?->getDateLastRead()?->format(DATE_ATOM),
        ];
    }
}

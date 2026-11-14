<?php

namespace Wexample\SymfonyConversation\Api\Normalizer\Entity\Conversation;

use ArrayObject;
use Symfony\Bundle\SecurityBundle\Security;
use Wexample\SymfonyConversation\Api\Dto\ConversationDto;
use Wexample\SymfonyConversation\Api\Normalizer\Entity\ConversationParticipant\DefaultParticipantNormalizer;
use Wexample\SymfonyConversation\Entity\Conversation;
use Wexample\SymfonyConversation\Entity\ConversationParticipant;
use Wexample\SymfonyConversation\Entity\Traits\Manipulator\ConversationEntityManipulatorTrait;
use Wexample\SymfonyConversation\Entity\UserConversationParticipant;
use Wexample\SymfonyConversation\Repository\MessageRepository;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;
use Wexample\SymfonyHelpers\Interface\NormalizableDataInterface;
use Wexample\SymfonyHelpers\Normalizer\AbstractEntityNormalizer;

/**
 * A conversation as the reader asking for it takes part in it: their own
 * participant, its last line, what they have not read.
 */
class DefaultConversationNormalizer extends AbstractEntityNormalizer
{
    use ConversationEntityManipulatorTrait;

    public function __construct(
        private readonly DefaultParticipantNormalizer $participantNormalizer,
        private readonly MessageRepository $messages,
        private readonly Security $security,
    ) {
    }

    public function normalizeEntity(
        Conversation|AbstractEntity $entity,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|NormalizableDataInterface|null {
        $participants = $entity->getParticipants()->toArray();

        $dto = $this->createDto(
            $entity,
            array_values(array_map(
                fn ($participant) => $this->participantNormalizer->flatten($participant),
                $participants
            ))
        );

        $user = $this->security->getUser();
        $me = $user instanceof UserEntityInterface
            ? $this->participantOf($participants, $user)
            : null;

        if ($me) {
            $dto->me = (string) $me->getId();
            $dto->unread = $this->messages->countUnreadFor($me);
        }

        $last = $this->messages->findOneBy(['conversation' => $entity], ['dateCreated' => 'DESC', 'id' => 'DESC']);
        $dto->lastMessage = $last?->getBody();

        return $dto;
    }

    /**
     * The reader's own participant, found among those just loaded for the dto
     * rather than asked for again: one query less per conversation of a list.
     * Compared by identity, which reading off a user not loaded yet does not
     * load.
     *
     * @param ConversationParticipant[] $participants
     */
    private function participantOf(
        array $participants,
        UserEntityInterface $user
    ): ?UserConversationParticipant {
        $id = (string) $user->getId();

        foreach ($participants as $participant) {
            if ($participant instanceof UserConversationParticipant && (string) $participant->getUser()->getId() === $id) {
                return $participant;
            }
        }

        return null;
    }

    /** The dto of its kind: a kind saying more builds its own, on this one. */
    protected function createDto(Conversation $conversation, array $participants): ConversationDto
    {
        return ConversationDto::fromConversation($conversation, $participants);
    }
}

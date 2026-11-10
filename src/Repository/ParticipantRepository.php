<?php

namespace Wexample\SymfonyConversation\Repository;

use Wexample\SymfonyConversation\Entity\Conversation;
use Wexample\SymfonyConversation\Entity\ConversationParticipant;
use Wexample\SymfonyConversation\Entity\Traits\Manipulator\ParticipantEntityManipulatorTrait;
use Wexample\SymfonyHelpers\Repository\AbstractRepository;

/**
 * @method ConversationParticipant|null find($id, $lockMode = null, $lockVersion = null)
 */
class ParticipantRepository extends AbstractRepository
{
    use ParticipantEntityManipulatorTrait;

    /**
     * Who takes part in the conversation, in the order they joined.
     *
     * @return ConversationParticipant[]
     */
    public function findByConversation(Conversation $conversation): array
    {
        return $this->findBy(['conversation' => $conversation], ['dateJoined' => self::SORT_ASC]);
    }
}

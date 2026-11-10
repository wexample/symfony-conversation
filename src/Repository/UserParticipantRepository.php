<?php

namespace Wexample\SymfonyConversation\Repository;

use Wexample\SymfonyConversation\Entity\Conversation;
use Wexample\SymfonyConversation\Entity\Traits\Manipulator\UserParticipantEntityManipulatorTrait;
use Wexample\SymfonyConversation\Entity\UserConversationParticipant;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;
use Wexample\SymfonyHelpers\Repository\AbstractRepository;

class UserParticipantRepository extends AbstractRepository
{
    use UserParticipantEntityManipulatorTrait;

    public function findOneByConversationAndUser(Conversation $conversation, UserEntityInterface $user): ?UserConversationParticipant
    {
        return $this->findOneBy(['conversation' => $conversation, 'user' => $user]);
    }
}

<?php

namespace Wexample\SymfonyConversation\Entity\Traits\Manipulator;

use Wexample\SymfonyConversation\Entity\UserConversationParticipant;
use Wexample\SymfonyHelpers\Entity\Traits\Manipulator\EntityManipulatorTrait;

trait UserParticipantEntityManipulatorTrait
{
    use EntityManipulatorTrait;

    public static function getEntityClassName(): string
    {
        return UserConversationParticipant::class;
    }
}

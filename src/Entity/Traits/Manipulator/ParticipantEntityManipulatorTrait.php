<?php

namespace Wexample\SymfonyConversation\Entity\Traits\Manipulator;

use Wexample\SymfonyConversation\Entity\ConversationParticipant;
use Wexample\SymfonyHelpers\Entity\Traits\Manipulator\EntityManipulatorTrait;

trait ParticipantEntityManipulatorTrait
{
    use EntityManipulatorTrait;

    public static function getEntityClassName(): string
    {
        return ConversationParticipant::class;
    }
}

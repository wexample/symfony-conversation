<?php

namespace Wexample\SymfonyConversation\Entity\Traits\Manipulator;

use Wexample\SymfonyConversation\Entity\Conversation;
use Wexample\SymfonyHelpers\Entity\Traits\Manipulator\EntityManipulatorTrait;

trait ConversationEntityManipulatorTrait
{
    use EntityManipulatorTrait;

    public static function getEntityClassName(): string
    {
        return Conversation::class;
    }
}

<?php

namespace Wexample\SymfonyConversation\Entity\Traits\Manipulator;

use Wexample\SymfonyConversation\Entity\ConversationMessage;
use Wexample\SymfonyHelpers\Entity\Traits\Manipulator\EntityManipulatorTrait;

trait MessageEntityManipulatorTrait
{
    use EntityManipulatorTrait;

    public static function getEntityClassName(): string
    {
        return ConversationMessage::class;
    }
}

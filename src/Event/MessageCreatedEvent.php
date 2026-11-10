<?php

namespace Wexample\SymfonyConversation\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Wexample\SymfonyConversation\Entity\ConversationMessage;

/**
 * Said once a line has been written, for whoever answers it: an engine for an
 * agent taking part, a notification for the people it names. The package
 * writes the line and stops there.
 */
class MessageCreatedEvent extends Event
{
    public function __construct(
        public readonly ConversationMessage $message,
    ) {
    }
}

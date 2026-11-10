<?php

namespace Wexample\SymfonyConversation\Interface;

use Wexample\SymfonyConversation\Entity\ConversationParticipant;

/**
 * How a participant reads in a conversation: what the thread, the lists and
 * the mentions draw of it — the person the design system's `avatar` and
 * `mention` take.
 */
interface ConversationPersonPresenterInterface
{
    /**
     * @return array{id: string, label: string, handle: ?string, src: ?string, emoji: ?string, marker: ?string}
     */
    public function present(ConversationParticipant $participant): array;
}

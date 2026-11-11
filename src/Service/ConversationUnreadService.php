<?php

namespace Wexample\SymfonyConversation\Service;

use Wexample\SymfonyConversation\Repository\MessageRepository;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;
use Wexample\SymfonyLive\Enum\LiveTopicAction;
use Wexample\SymfonyLive\Helper\LiveTopicHelper;
use Wexample\SymfonyLive\Service\LivePublisherService;

/**
 * How many lines wait for a person, over all their conversations, and the
 * saying of it as it changes — a line written to them, a conversation read.
 *
 * Said on the person's own topic and not on a conversation's: whoever shows
 * the count — a pill on the way to the conversations — has none of them
 * open. Heard where the application opens its accounts to subscription
 * (`#[LiveEntity]` on its user, a voter letting each follow their own);
 * said to nobody elsewhere.
 */
final readonly class ConversationUnreadService
{
    final public const string EVENT_UNREAD = 'conversation-unread';

    public function __construct(
        private MessageRepository $messages,
        private LivePublisherService $publisher,
    ) {
    }

    public function countFor(UserEntityInterface $user): int
    {
        return $this->messages->countUnreadForUser($user);
    }

    public function publishFor(UserEntityInterface $user): void
    {
        $this->publisher->publishEvent(
            LiveTopicHelper::entity($user, LiveTopicAction::EVENT),
            self::EVENT_UNREAD,
            ['unread' => $this->countFor($user)]
        );
    }
}

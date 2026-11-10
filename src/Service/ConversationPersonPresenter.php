<?php

namespace Wexample\SymfonyConversation\Service;

use Wexample\SymfonyConversation\Entity\ConversationParticipant;
use Wexample\SymfonyConversation\Interface\ConversationPersonPresenterInterface;

/**
 * A participant by its label alone: the design system draws its initials. An
 * application with faces, handles or emojis of its own aliases its presenter
 * in place of this one.
 */
final readonly class ConversationPersonPresenter implements ConversationPersonPresenterInterface
{
    public function present(ConversationParticipant $participant): array
    {
        return [
            'id' => (string) $participant->getId(),
            'label' => $participant->getLabel(),
            'handle' => null,
            'src' => null,
            'emoji' => null,
            'marker' => null,
        ];
    }
}

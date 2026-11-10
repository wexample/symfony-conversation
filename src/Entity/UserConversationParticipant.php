<?php

namespace Wexample\SymfonyConversation\Entity;

use Doctrine\ORM\Mapping as ORM;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyConversation\Repository\UserParticipantRepository;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;

/**
 * A person of the application taking part in a conversation, once at most in
 * each (ConversationService::join: the conversation lives in the root's
 * table, no constraint of this one's can hold the pair).
 */
#[PseudocodeExport(inherited: true)]
#[ORM\Entity(repositoryClass: UserParticipantRepository::class)]
#[ORM\Table(name: 'conversation_user_participant')]
class UserConversationParticipant extends ConversationParticipant
{
    #[ORM\ManyToOne(targetEntity: UserEntityInterface::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected UserEntityInterface $user;

    public function __construct(Conversation $conversation, UserEntityInterface $user)
    {
        $this->user = $user;

        parent::__construct($conversation);
    }

    public function getUser(): UserEntityInterface
    {
        return $this->user;
    }

    public function getLabel(): string
    {
        return method_exists($this->user, 'getDisplayName')
            ? $this->user->getDisplayName()
            : (string) $this->user->getId();
    }
}

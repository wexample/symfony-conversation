<?php

namespace Wexample\SymfonyConversation\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Wexample\SymfonyConversation\Entity\Traits\Manipulator\ConversationEntityManipulatorTrait;
use Wexample\SymfonyConversation\Repository\UserParticipantRepository;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;
use Wexample\SymfonyHelpers\Voter\AbstractEntityVoter;

/**
 * A conversation, of whatever kind, is its participants': read, written in and
 * followed live (symfony-live asks VIEW) by the people taking part, nobody
 * else.
 */
class ConversationVoter extends AbstractEntityVoter
{
    use ConversationEntityManipulatorTrait;

    public function __construct(
        private readonly UserParticipantRepository $participants,
    ) {
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        $user = $token->getUser();

        return $user instanceof UserEntityInterface
            && null !== $this->participants->findOneByConversationAndUser($subject, $user);
    }
}

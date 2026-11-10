<?php

namespace Wexample\SymfonyConversation\Service;

use Doctrine\ORM\EntityManagerInterface;
use Wexample\Helpers\Helper\ClassHelper;
use Wexample\SymfonyConversation\Entity\Conversation;
use Wexample\SymfonyConversation\Entity\UserConversationParticipant;
use Wexample\SymfonyConversation\Repository\UserParticipantRepository;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;

/**
 * What is done to conversations between people: one opened between two, a
 * person joining one, what a person read of one.
 */
final readonly class ConversationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserParticipantRepository $participants,
    ) {
    }

    /**
     * The conversation of the two people alone, the one they already hold
     * when there is one: a second would split what they say to each other.
     */
    public function openWith(UserEntityInterface $user, UserEntityInterface $other): Conversation
    {
        if ($existing = $this->findBetween($user, $other)) {
            return $existing;
        }

        $conversation = new Conversation();
        $this->entityManager->persist($conversation);
        $this->join($conversation, $user)->setRole(UserConversationParticipant::ROLE_OWNER);

        if (! $user->getId()->equals($other->getId())) {
            $this->join($conversation, $other);
        }

        $this->entityManager->flush();

        return $conversation;
    }

    /** The person in the conversation, once: already in, as they were. */
    public function join(Conversation $conversation, UserEntityInterface $user): UserConversationParticipant
    {
        if ($participant = $this->participants->findOneByConversationAndUser($conversation, $user)) {
            return $participant;
        }

        $participant = new UserConversationParticipant($conversation, $user);
        $this->entityManager->persist($participant);

        return $participant;
    }

    /** What the person read of the conversation: all of it, as of now. */
    public function markRead(UserConversationParticipant $participant): void
    {
        $participant->setDateLastRead(new \DateTime());
        $this->entityManager->flush();
    }

    /**
     * The conversation between people the two alone take part in, of the
     * root kind: one an engine holds — a subclass — is the engine's.
     */
    private function findBetween(UserEntityInterface $user, UserEntityInterface $other): ?Conversation
    {
        $candidates = $this->entityManager->createQueryBuilder()
            ->select('conversation')
            ->from(Conversation::class, 'conversation')
            ->innerJoin(UserConversationParticipant::class, 'mine', 'ON', 'mine.conversation = conversation AND mine.user = :user')
            ->innerJoin(UserConversationParticipant::class, 'theirs', 'ON', 'theirs.conversation = conversation AND theirs.user = :other')
            ->andWhere('SIZE(conversation.participants) = :count')
            ->setParameter('user', $user)
            ->setParameter('other', $other)
            ->setParameter('count', $user->getId()->equals($other->getId()) ? 1 : 2)
            ->getQuery()
            ->getResult();

        foreach ($candidates as $candidate) {
            if (Conversation::class === ClassHelper::getRealClassPath($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}

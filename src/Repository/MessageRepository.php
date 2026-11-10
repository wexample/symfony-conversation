<?php

namespace Wexample\SymfonyConversation\Repository;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Wexample\SymfonyConversation\Entity\Conversation;
use Wexample\SymfonyConversation\Entity\ConversationMessage;
use Wexample\SymfonyConversation\Entity\ConversationParticipant;
use Wexample\SymfonyConversation\Entity\Traits\Manipulator\MessageEntityManipulatorTrait;
use Wexample\SymfonyConversation\Event\MessageCreatedEvent;
use Wexample\SymfonyHelpers\Repository\AbstractRepository;

/**
 * @method ConversationMessage|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConversationMessage|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConversationMessage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MessageRepository extends AbstractRepository
{
    use MessageEntityManipulatorTrait;

    public function __construct(
        ManagerRegistry $registry,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
        parent::__construct($registry);
    }

    /**
     * An empty line of a conversation, for an engine's transcript to fill:
     * what it says, when, by whom, is the transcript's to dictate.
     */
    public function createNewMessageFromTranscript(Conversation $conversation): ConversationMessage
    {
        return new ConversationMessage($conversation);
    }

    public function createNewMessage(
        Conversation $conversation,
        string $type,
        string $body,
        ?ConversationParticipant $author = null,
    ): ConversationMessage {
        $message = new ConversationMessage($conversation);
        $message->setType($type);
        $message->setBody($body);
        $message->setAuthor($author);
        $message->setDateCreatedNow();

        return $message;
    }

    /**
     * Writes a line, the conversation's last one from then, and says so for
     * whoever answers it — once written, as what listens reads it back.
     *
     * @param ConversationParticipant[] $mentions
     */
    public function saveNewMessageAndPushEvent(
        Conversation $conversation,
        string $type,
        string $body,
        ?ConversationParticipant $author = null,
        ?string $providerMessageIdentifier = null,
        array $mentions = [],
    ): ConversationMessage {
        $message = $this->createNewMessage($conversation, $type, $body, $author);
        // Unique: what names the line twice is written once, the second
        // refused by the database.
        $message->setProviderMessageIdentifier($providerMessageIdentifier);

        foreach ($mentions as $mentioned) {
            $message->addMention($mentioned);
        }

        $conversation->setDateLastMessage($message->getDateCreated());
        $this->save($message);

        $this->eventDispatcher->dispatch(new MessageCreatedEvent($message));

        return $message;
    }

    /**
     * The conversation as it was held, oldest first, a page at a time: one
     * turn writes several lines within a second, so the identifier — given in
     * creation order — settles what the date cannot.
     */
    public function queryByConversationOldestFirst(Conversation $conversation): QueryBuilder
    {
        return $this
            ->queryByField('conversation', $conversation)
            ->orderBy($this->getEntityQueryAlias().'.dateCreated', self::SORT_ASC)
            ->addOrderBy($this->getEntityQueryAlias().'.id', self::SORT_ASC);
    }

    /** @return ConversationMessage[] */
    public function findByConversation(Conversation $conversation): array
    {
        return $this->queryByConversationOldestFirst($conversation)->getQuery()->getResult();
    }

    public function findOneByProviderMessageIdentifier(string $identifier): ?ConversationMessage
    {
        return $this->findOneBy(['providerMessageIdentifier' => $identifier]);
    }

    /**
     * A line written before its engine confirmed it, waiting for the one that
     * says the same: what was said is the only handle until the engine names it.
     */
    public function findOneUnconfirmed(
        Conversation $conversation,
        string $type,
        string $body,
    ): ?ConversationMessage {
        return $this->findOneBy([
            'conversation' => $conversation,
            'type' => $type,
            'body' => $body,
            'providerMessageIdentifier' => null,
        ]);
    }

    /** What others wrote in the conversation since the participant last read it. */
    public function countUnreadFor(ConversationParticipant $participant): int
    {
        $alias = $this->getEntityQueryAlias();
        $builder = $this->queryByField('conversation', $participant->getConversation())
            ->select('COUNT('.$alias.'.id)')
            ->andWhere($alias.'.author IS NULL OR '.$alias.'.author != :participant')
            ->setParameter('participant', $participant);

        if ($read = $participant->getDateLastRead()) {
            $builder->andWhere($alias.'.dateCreated > :read')->setParameter('read', $read);
        }

        return (int) $builder->getQuery()->getSingleScalarResult();
    }
}

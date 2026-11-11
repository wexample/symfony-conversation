<?php

namespace Wexample\SymfonyConversation\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Contracts\Service\ResetInterface;
use Wexample\SymfonyConversation\Api\Normalizer\Entity\ConversationMessage\DefaultMessageNormalizer;
use Wexample\SymfonyConversation\Entity\ConversationMessage;
use Wexample\SymfonyConversation\Entity\UserConversationParticipant;
use Wexample\SymfonyConversation\Repository\ParticipantRepository;
use Wexample\SymfonyConversation\Service\ConversationUnreadService;
use Wexample\SymfonyLive\Enum\LiveTopicAction;
use Wexample\SymfonyLive\Helper\LiveTopicHelper;
use Wexample\SymfonyLive\Service\LivePublisherService;

/**
 * Says on the conversation's topic that a line was written, whoever wrote it.
 * And to each of the people it was written to, on their own topic, how many
 * lines now wait for them (ConversationUnreadService).
 *
 * Published on the conversation and not on the line: a reader opens it before
 * the next line exists. Doctrine is listened to rather than the repository, as
 * an engine filling the thread from a transcript persists the rows itself.
 * Publication waits for postFlush: a line announced then rolled back is one no
 * subscriber can un-hear.
 */
#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postFlush)]
class MessagePublishListener implements ResetInterface
{
    final public const string EVENT_MESSAGE_CREATED = 'message-created';

    /** @var ConversationMessage[] */
    private array $pending = [];

    public function __construct(
        private readonly LivePublisherService $publisher,
        private readonly DefaultMessageNormalizer $normalizer,
        private readonly ParticipantRepository $participants,
        private readonly ConversationUnreadService $unread,
    ) {
    }

    /** Emptied by the flush publishing them, or for the next request. */
    public function reset(): void
    {
        $this->pending = [];
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof ConversationMessage) {
            $this->pending[] = $entity;
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        $pending = $this->pending;
        $this->pending = [];
        $readers = [];

        foreach ($pending as $message) {
            $this->publisher->publishEvent(
                LiveTopicHelper::entity($message->getConversation(), LiveTopicAction::EVENT),
                self::EVENT_MESSAGE_CREATED,
                $this->normalizer->normalize($message)
            );

            // Asked rather than read off the conversation: someone who just
            // joined is not in the collection it was loaded with.
            foreach ($this->participants->findByConversation($message->getConversation()) as $participant) {
                if ($participant instanceof UserConversationParticipant && $participant !== $message->getAuthor()) {
                    $readers[(string) $participant->getUser()->getId()] = $participant->getUser();
                }
            }
        }

        // Once each, however many lines the flush wrote them.
        foreach ($readers as $reader) {
            $this->unread->publishFor($reader);
        }
    }
}

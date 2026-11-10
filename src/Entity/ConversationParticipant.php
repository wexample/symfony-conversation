<?php

namespace Wexample\SymfonyConversation\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyApi\Attribute\ApiEntity;
use Wexample\SymfonyConversation\Repository\ParticipantRepository;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * Someone taking part in a conversation: a person (UserConversationParticipant),
 * an agent (symfony-ai-conversation), each kind in a table of its own joined
 * to this one. What every kind has: the conversation, the part it plays, when
 * it joined, and what it last read — where « new messages » begins for it.
 */
#[ApiEntity]
#[PseudocodeExport(inherited: true)]
#[ORM\Entity(repositoryClass: ParticipantRepository::class)]
#[ORM\Table(name: 'conversation_participant')]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'kind', type: Types::STRING, length: 32)]
abstract class ConversationParticipant extends AbstractEntity
{
    final public const string ROLE_MEMBER = 'member';

    final public const string ROLE_OWNER = 'owner';

    #[ORM\ManyToOne(targetEntity: Conversation::class, inversedBy: 'participants')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected Conversation $conversation;

    #[ORM\Column(type: Types::STRING, length: 32)]
    protected string $role = self::ROLE_MEMBER;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    protected DateTimeInterface $dateJoined;

    /** The last message it read up to, null while it read none. */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $dateLastRead = null;

    public function __construct(Conversation $conversation)
    {
        parent::__construct();

        $this->conversation = $conversation;
        $this->dateJoined = new \DateTime();
        $conversation->addParticipant($this);
    }

    /** What the participant is called in the conversation. */
    abstract public function getLabel(): string;

    public function getConversation(): Conversation
    {
        return $this->conversation;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getDateJoined(): DateTimeInterface
    {
        return $this->dateJoined;
    }

    public function getDateLastRead(): ?DateTimeInterface
    {
        return $this->dateLastRead;
    }

    public function setDateLastRead(?DateTimeInterface $dateLastRead): static
    {
        $this->dateLastRead = $dateLastRead;

        return $this;
    }
}

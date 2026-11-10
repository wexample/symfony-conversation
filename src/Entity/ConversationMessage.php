<?php

namespace Wexample\SymfonyConversation\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyApi\Attribute\ApiEntity;
use Wexample\SymfonyConversation\Repository\MessageRepository;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Entity\Traits\HasBodyTrait;
use Wexample\SymfonyHelpers\Entity\Traits\HasDateCreatedTrait;
use Wexample\SymfonyHelpers\Entity\Traits\HasTypeTrait;

/**
 * One line of a conversation: who wrote it, what it says, when, whom it names.
 *
 * Its author is a participant — a person, an agent —, none for what the
 * conversation says of itself (`system`). Its type says what the line is
 * rather than who wrote it: words (`text`), a failure (`error`), a tool called
 * or answered (`tool`, its body json). `providerMessageIdentifier` is what an
 * engine calls the line, unique so that the same line received twice is
 * written once.
 */
#[ApiEntity]
#[PseudocodeExport(inherited: true)]
#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'conversation_message')]
#[ORM\Index(name: 'IDX_CONVERSATION_MESSAGE_ORDER', columns: ['conversation_id', 'date_created'])]
class ConversationMessage extends AbstractEntity
{
    use HasBodyTrait;
    use HasDateCreatedTrait;
    use HasTypeTrait;

    public const string TYPE_ERROR = 'error';
    public const string TYPE_SYSTEM = 'system';
    public const string TYPE_TEXT = 'text';
    public const string TYPE_TOOL = 'tool';

    #[ORM\ManyToOne(targetEntity: Conversation::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected Conversation $conversation;

    /** Who wrote it; its line kept when the participant leaves. */
    #[ORM\ManyToOne(targetEntity: ConversationParticipant::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    protected ?ConversationParticipant $author = null;

    /** @var Collection<int, ConversationParticipant> the participants it names */
    #[ORM\ManyToMany(targetEntity: ConversationParticipant::class)]
    #[ORM\JoinTable(name: 'conversation_message_mention')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(onDelete: 'CASCADE')]
    protected Collection $mentions;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true, nullable: true)]
    protected ?string $providerMessageIdentifier = null;

    /** What the line cost, when an engine says. */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    protected ?int $tokens = null;

    public function __construct(Conversation $conversation)
    {
        parent::__construct();

        $this->conversation = $conversation;
        $this->mentions = new ArrayCollection();
    }

    /** @return string[] */
    public static function getAllowedTypes(): ?array
    {
        return [
            self::TYPE_ERROR,
            self::TYPE_SYSTEM,
            self::TYPE_TEXT,
            self::TYPE_TOOL,
        ];
    }

    public function getConversation(): Conversation
    {
        return $this->conversation;
    }

    public function getAuthor(): ?ConversationParticipant
    {
        return $this->author;
    }

    public function setAuthor(?ConversationParticipant $author): static
    {
        $this->author = $author;

        return $this;
    }

    /** @return Collection<int, ConversationParticipant> */
    public function getMentions(): Collection
    {
        return $this->mentions;
    }

    public function addMention(ConversationParticipant $participant): static
    {
        if (! $this->mentions->contains($participant)) {
            $this->mentions->add($participant);
        }

        return $this;
    }

    public function getProviderMessageIdentifier(): ?string
    {
        return $this->providerMessageIdentifier;
    }

    public function setProviderMessageIdentifier(?string $providerMessageIdentifier): static
    {
        $this->providerMessageIdentifier = $providerMessageIdentifier;

        return $this;
    }

    public function getTokens(): ?int
    {
        return $this->tokens;
    }

    public function setTokens(?int $tokens): static
    {
        $this->tokens = $tokens;

        return $this;
    }
}

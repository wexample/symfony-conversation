<?php

namespace Wexample\SymfonyConversation\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyApi\Attribute\ApiEntity;
use Wexample\SymfonyConversation\Repository\ConversationRepository;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Entity\Traits\HasDateCreatedTrait;
use Wexample\SymfonyLive\Attribute\LiveEntity;
use Wexample\SymfonyLive\Enum\LiveTopicAction;

/**
 * A conversation held in the application: who takes part in it — people,
 * agents —, and when it last spoke. What it is held for — a project, a
 * session on a remote engine — is the subclass's, each in a table of its own
 * joined to this one; the root is the conversation between people, as is.
 *
 * It is the participants', and theirs alone: read, written in and followed
 * live by them (ConversationVoter).
 */
#[ApiEntity]
#[LiveEntity(actions: [LiveTopicAction::EVENT])]
#[PseudocodeExport(inherited: true)]
#[ORM\Entity(repositoryClass: ConversationRepository::class)]
#[ORM\Table(name: 'conversation')]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'kind', type: Types::STRING, length: 32)]
class Conversation extends AbstractEntity
{
    use HasDateCreatedTrait;

    /** What it is called, null while it has no name. */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $title = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $dateLastMessage = null;

    /** @var Collection<int, ConversationParticipant> */
    #[ORM\OneToMany(targetEntity: ConversationParticipant::class, mappedBy: 'conversation')]
    protected Collection $participants;

    public function __construct()
    {
        parent::__construct();

        $this->participants = new ArrayCollection();
        $this->setDateCreatedNow();
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDateLastMessage(): ?DateTimeInterface
    {
        return $this->dateLastMessage;
    }

    public function setDateLastMessage(?DateTimeInterface $dateLastMessage): static
    {
        $this->dateLastMessage = $dateLastMessage;

        return $this;
    }

    /** @return Collection<int, ConversationParticipant> */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(ConversationParticipant $participant): static
    {
        if (! $this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(ConversationParticipant $participant): static
    {
        $this->participants->removeElement($participant);

        return $this;
    }
}

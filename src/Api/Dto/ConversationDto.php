<?php

namespace Wexample\SymfonyConversation\Api\Dto;

use Wexample\SymfonyApi\Api\Dto\AbstractEntityDto;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * A conversation as one of its people reads it: with whom, its last line,
 * what they have not read of it.
 */
class ConversationDto extends AbstractEntityDto
{
    public ?string $title;

    public ?string $dateCreated;

    public ?string $dateLastMessage;

    /** @var array<int, array> the participants, as they are drawn */
    public array $participants = [];

    /** The reader's own participant, by id: null for a reader taking no part. */
    public ?string $me = null;

    public ?string $lastMessage = null;

    public int $unread = 0;

    /**
     * @param \Wexample\SymfonyConversation\Entity\Conversation $entity
     */
    public static function fromConversation(AbstractEntity $entity, array $participants): static
    {
        $dto = parent::fromEntity($entity);

        $dto->title = $entity->getTitle();
        $dto->dateCreated = $entity->getDateCreated()?->format(DATE_ATOM);
        $dto->dateLastMessage = $entity->getDateLastMessage()?->format(DATE_ATOM);
        $dto->participants = $participants;

        return $dto;
    }
}

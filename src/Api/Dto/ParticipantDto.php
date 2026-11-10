<?php

namespace Wexample\SymfonyConversation\Api\Dto;

use Wexample\SymfonyApi\Api\Dto\AbstractEntityDto;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

/**
 * A participant as the browser draws it: the person the design system's
 * `avatar`, lists and mentions take, with its part in the conversation.
 */
class ParticipantDto extends AbstractEntityDto
{
    public string $conversation;

    public string $role;

    public string $label;

    public ?string $handle = null;

    public ?string $src = null;

    public ?string $emoji = null;

    /** What tells it apart beside its name — an agent. */
    public ?string $marker = null;

    public ?string $dateLastRead = null;

    /**
     * @param \Wexample\SymfonyConversation\Entity\ConversationParticipant $entity
     * @param array{label: string, handle: ?string, src: ?string, emoji: ?string, marker: ?string} $person
     */
    public static function fromParticipant(AbstractEntity $entity, array $person): self
    {
        $dto = parent::fromEntity($entity);

        $dto->conversation = (string) $entity->getConversation()->getId();
        $dto->role = $entity->getRole();
        $dto->label = $person['label'];
        $dto->handle = $person['handle'];
        $dto->src = $person['src'];
        $dto->emoji = $person['emoji'];
        $dto->marker = $person['marker'];
        $dto->dateLastRead = $entity->getDateLastRead()?->format(DATE_ATOM);

        return $dto;
    }
}

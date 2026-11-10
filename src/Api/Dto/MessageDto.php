<?php

namespace Wexample\SymfonyConversation\Api\Dto;

use Wexample\SymfonyApi\Api\Dto\AbstractEntityDto;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;

class MessageDto extends AbstractEntityDto
{
    /** Named after the property of the entity, which is what the browser checks against. */
    public string $conversation;

    /** The participant who wrote it, as it is drawn; null for the conversation's own line. */
    public ?array $author = null;

    /** `text`, `system`, `error`, `tool`. */
    public ?string $type;

    public ?string $body;

    public ?string $dateCreated;

    public ?int $tokens;

    public ?string $providerMessageIdentifier;

    /** @var string[] the participants it names, by id */
    public array $mentions = [];

    /**
     * @param \Wexample\SymfonyConversation\Entity\ConversationMessage $entity
     */
    public static function fromMessage(AbstractEntity $entity, ?array $author): self
    {
        $dto = parent::fromEntity($entity);

        $dto->conversation = (string) $entity->getConversation()->getId();
        $dto->author = $author;
        $dto->type = $entity->getType();
        $dto->body = $entity->getBody();
        $dto->dateCreated = $entity->getDateCreated()?->format(DATE_ATOM);
        $dto->tokens = $entity->getTokens();
        $dto->providerMessageIdentifier = $entity->getProviderMessageIdentifier();

        foreach ($entity->getMentions() as $mentioned) {
            $dto->mentions[] = (string) $mentioned->getId();
        }

        return $dto;
    }
}

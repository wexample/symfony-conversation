<?php

namespace Wexample\SymfonyConversation\Api\Normalizer\Entity\ConversationMessage;

use ArrayObject;
use Wexample\SymfonyConversation\Api\Dto\MessageDto;
use Wexample\SymfonyConversation\Api\Normalizer\Entity\ConversationParticipant\DefaultParticipantNormalizer;
use Wexample\SymfonyConversation\Entity\ConversationMessage;
use Wexample\SymfonyConversation\Entity\Traits\Manipulator\MessageEntityManipulatorTrait;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Interface\NormalizableDataInterface;
use Wexample\SymfonyHelpers\Normalizer\AbstractEntityNormalizer;

class DefaultMessageNormalizer extends AbstractEntityNormalizer
{
    use MessageEntityManipulatorTrait;

    public function __construct(
        private readonly DefaultParticipantNormalizer $participantNormalizer,
    ) {
    }

    public function normalizeEntity(
        ConversationMessage|AbstractEntity $entity,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|NormalizableDataInterface|null {
        $author = $entity->getAuthor();

        return MessageDto::fromMessage(
            $entity,
            $author ? $this->participantNormalizer->flatten($author) : null
        );
    }
}

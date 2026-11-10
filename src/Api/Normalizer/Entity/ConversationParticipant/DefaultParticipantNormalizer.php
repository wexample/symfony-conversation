<?php

namespace Wexample\SymfonyConversation\Api\Normalizer\Entity\ConversationParticipant;

use ArrayObject;
use Wexample\SymfonyConversation\Api\Dto\ParticipantDto;
use Wexample\SymfonyConversation\Entity\ConversationParticipant;
use Wexample\SymfonyConversation\Entity\Traits\Manipulator\ParticipantEntityManipulatorTrait;
use Wexample\SymfonyConversation\Interface\ConversationPersonPresenterInterface;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Helper\EntityHierarchyHelper;
use Wexample\SymfonyHelpers\Interface\NormalizableDataInterface;
use Wexample\SymfonyHelpers\Normalizer\AbstractEntityNormalizer;

class DefaultParticipantNormalizer extends AbstractEntityNormalizer
{
    use ParticipantEntityManipulatorTrait;

    public function __construct(
        private readonly ConversationPersonPresenterInterface $presenter,
    ) {
    }

    public function normalizeEntity(
        ConversationParticipant|AbstractEntity $entity,
        ?string $format = null,
        array $context = []
    ): array|string|int|float|bool|ArrayObject|NormalizableDataInterface|null {
        return ParticipantDto::fromParticipant($entity, $this->presenter->present($entity));
    }

    /**
     * The participant as a line, a list or a mention draws it, flat: what a
     * message or a conversation carries of its people, not an entity of the
     * api of its own.
     *
     * @return array<string, mixed>
     */
    public function flatten(ConversationParticipant $participant): array
    {
        return ParticipantDto::fromParticipant($participant, $this->presenter->present($participant))->toArray()
            + ['kind' => EntityHierarchyHelper::getDiscriminatorValue($participant)];
    }
}

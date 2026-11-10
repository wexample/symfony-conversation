<?php

namespace Wexample\SymfonyConversation\Api\Dto\Entity\ConversationMessage;

use Symfony\Component\Validator\Constraints\Type;
use Wexample\SymfonyApi\Api\Attribute\RequiredDtoProperty;
use Wexample\SymfonyApi\Api\Dto\AbstractDto;
use Wexample\SymfonyHelpers\Helper\VariableHelper;

/**
 * What is said to write in a conversation: which one, what, whom it names.
 * Neither its author nor its type is asked: a line written here is the words
 * of the person sending it, and letting the caller name another would let it
 * speak for them.
 */
class CreateMessageDto extends AbstractDto
{
    #[Type(VariableHelper::VARIABLE_TYPE_STRING)]
    #[RequiredDtoProperty]
    public string $conversation;

    #[Type(VariableHelper::VARIABLE_TYPE_STRING)]
    public ?string $body = null;

    /** @var string[]|null the participants named, by id */
    #[Type(VariableHelper::VARIABLE_TYPE_ARRAY)]
    public ?array $mentions = null;
}

<?php

namespace Wexample\SymfonyConversation\Api\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\LengthQueryOption;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\PageQueryOption;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\StringQueryOption;
use Wexample\SymfonyApi\Api\Attribute\ValidateRequestContent;
use Wexample\SymfonyApi\Api\Class\ApiResponse;
use Wexample\SymfonyApi\Api\Controller\AbstractApiController;
use Wexample\SymfonyConversation\Api\Dto\Entity\ConversationMessage\CreateMessageDto;
use Wexample\SymfonyConversation\Api\Normalizer\Entity\ConversationMessage\DefaultMessageNormalizer;
use Wexample\SymfonyConversation\Entity\ConversationMessage;
use Wexample\SymfonyConversation\Repository\ConversationRepository;
use Wexample\SymfonyConversation\Repository\MessageRepository;
use Wexample\SymfonyConversation\Repository\ParticipantRepository;
use Wexample\SymfonyConversation\Repository\UserParticipantRepository;
use Wexample\SymfonyHelpers\Controller\AbstractController;
use Wexample\SymfonyHelpers\Voter\AbstractEntityVoter;

/**
 * What a conversation is read and written through, always by the
 * conversation: a line says nothing on its own.
 */
#[Route(path: '_api/conversation-message/', name: 'api_conversation_message_')]
class MessageController extends AbstractApiController
{
    final public const string QUERY_OPTION_CONVERSATION = 'conversation';

    final public const string ROUTE_CREATE = 'create';

    final public const string ROUTE_LIST = 'list';

    /**
     * Writes the words of the person sending them, and says so for whoever
     * answers — an agent taking part, the people named.
     */
    #[Route(path: 'create', name: self::ROUTE_CREATE, methods: [Request::METHOD_POST], options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    #[ValidateRequestContent(dto: CreateMessageDto::class, attributeName: 'createMessageDto')]
    public function create(
        CreateMessageDto $createMessageDto,
        ConversationRepository $conversationRepository,
        UserParticipantRepository $userParticipantRepository,
        ParticipantRepository $participantRepository,
        MessageRepository $messageRepository,
        DefaultMessageNormalizer $normalizer,
    ): ApiResponse {
        $conversation = $conversationRepository->find($createMessageDto->conversation);

        if (! $conversation) {
            return self::apiResponseError('Unknown conversation.');
        }

        $this->denyAccessUnlessGranted(AbstractEntityVoter::EDIT, $conversation);

        $body = trim((string) $createMessageDto->body);

        if ('' === $body) {
            return self::apiResponseError('A message needs a body.');
        }

        // Named among the conversation's own people, nobody else.
        $mentions = [];
        foreach ($createMessageDto->mentions ?? [] as $id) {
            $mentioned = is_string($id) ? $participantRepository->find($id) : null;

            if ($mentioned && $mentioned->getConversation() === $conversation) {
                $mentions[] = $mentioned;
            }
        }

        $message = $messageRepository->saveNewMessageAndPushEvent(
            conversation: $conversation,
            type: ConversationMessage::TYPE_TEXT,
            body: $body,
            author: $userParticipantRepository->findOneByConversationAndUser($conversation, $this->getUser()),
            mentions: $mentions,
        );

        return self::apiResponseSuccess(data: $normalizer->normalize($message));
    }

    #[Route(path: 'list', name: self::ROUTE_LIST, methods: AbstractController::ROUTE_OPTIONS_METHOD_ONLY_GET, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    #[PageQueryOption]
    #[LengthQueryOption]
    #[StringQueryOption(key: self::QUERY_OPTION_CONVERSATION, default: '')]
    public function list(
        Request $request,
        ConversationRepository $conversationRepository,
        MessageRepository $messageRepository,
        DefaultMessageNormalizer $normalizer,
    ): ApiResponse {
        $conversation = $conversationRepository->find(
            self::getQueryOptionValue($request, self::QUERY_OPTION_CONVERSATION, '')
        );

        if (! $conversation) {
            return self::apiResponseError('Unknown conversation.');
        }

        $this->denyAccessUnlessGranted(AbstractEntityVoter::VIEW, $conversation);

        $builder = $messageRepository->queryByConversationOldestFirst($conversation);

        $pagination = self::getQueryOptionPagination(
            request: $request,
            total: $messageRepository->countAll($builder)
        );

        return self::apiResponsePaginated(
            pagination: $pagination,
            items: $normalizer->normalizeCollection(
                $messageRepository->findPaginated(
                    page: $pagination->page,
                    length: $pagination->length,
                    builder: $builder
                )
            )
        );
    }
}

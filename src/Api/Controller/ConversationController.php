<?php

namespace Wexample\SymfonyConversation\Api\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;
use Wexample\SymfonyConversation\Api\Normalizer\Entity\Conversation\DefaultConversationNormalizer;
use Wexample\SymfonyConversation\Api\Normalizer\Entity\ConversationParticipant\DefaultParticipantNormalizer;
use Wexample\SymfonyConversation\Entity\Conversation;
use Wexample\SymfonyConversation\Repository\ConversationRepository;
use Wexample\SymfonyConversation\Repository\ParticipantRepository;
use Wexample\SymfonyConversation\Repository\UserParticipantRepository;
use Wexample\SymfonyConversation\Service\ConversationService;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\LengthQueryOption;
use Wexample\SymfonyApi\Api\Attribute\QueryOption\PageQueryOption;
use Wexample\SymfonyApi\Api\Class\ApiResponse;
use Wexample\SymfonyApi\Api\Controller\AbstractApiController;
use Wexample\SymfonyHelpers\Controller\AbstractController;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;
use Wexample\SymfonyHelpers\Voter\AbstractEntityVoter;

/**
 * The conversations of the person asking: those they take part in, who is in
 * one, one opened with someone, one read.
 */
#[Route(path: '_api/conversation/', name: 'api_conversation_')]
class ConversationController extends AbstractApiController
{
    /** Theirs, the one that last spoke first. */
    #[Route(path: 'list', name: 'list', methods: AbstractController::ROUTE_OPTIONS_METHOD_ONLY_GET, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    #[PageQueryOption]
    #[LengthQueryOption]
    public function list(
        Request $request,
        ConversationRepository $conversationRepository,
        DefaultConversationNormalizer $normalizer,
    ): ApiResponse {
        $user = $this->getUser();

        if (! $user instanceof UserEntityInterface) {
            throw $this->createAccessDeniedException();
        }

        $builder = $conversationRepository->queryForUser($user);

        $pagination = self::getQueryOptionPagination(
            request: $request,
            total: $conversationRepository->countAll($builder)
        );

        return self::apiResponsePaginated(
            pagination: $pagination,
            items: $normalizer->normalizeCollection(
                $conversationRepository->findPaginated(
                    page: $pagination->page,
                    length: $pagination->length,
                    builder: $builder
                )
            )
        );
    }

    /** The conversation of the asker and the person named, opened if they hold none. */
    #[Route(path: 'open/{user}', name: 'open', requirements: ['user' => Requirement::UUID], methods: [Request::METHOD_POST], options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function open(
        string $user,
        EntityManagerInterface $entityManager,
        ConversationService $conversationService,
        DefaultConversationNormalizer $normalizer,
    ): ApiResponse {
        $me = $this->getUser();

        if (! $me instanceof UserEntityInterface) {
            throw $this->createAccessDeniedException();
        }

        $other = $entityManager->getRepository(UserEntityInterface::class)->find($user)
            ?? throw new NotFoundHttpException('No user '.$user);

        return self::apiResponseSuccess(data: $normalizer->normalize($conversationService->openWith($me, $other)));
    }

    #[Route(path: '{id}', name: 'show', requirements: ['id' => Requirement::UUID], methods: AbstractController::ROUTE_OPTIONS_METHOD_ONLY_GET, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function show(
        string $id,
        ConversationRepository $conversationRepository,
        DefaultConversationNormalizer $normalizer,
    ): ApiResponse {
        $conversation = $this->findGranted($conversationRepository, $id, AbstractEntityVoter::VIEW);

        return self::apiResponseSuccess(data: $normalizer->normalize($conversation));
    }

    #[Route(path: '{id}/participants', name: 'participants', requirements: ['id' => Requirement::UUID], methods: AbstractController::ROUTE_OPTIONS_METHOD_ONLY_GET, options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function participants(
        string $id,
        ConversationRepository $conversationRepository,
        ParticipantRepository $participantRepository,
        DefaultParticipantNormalizer $normalizer,
    ): ApiResponse {
        $conversation = $this->findGranted($conversationRepository, $id, AbstractEntityVoter::VIEW);

        return self::apiResponseSuccess(data: $normalizer->normalizeCollection(
            $participantRepository->findByConversation($conversation)
        ));
    }

    /** Read by the asker, all of it, as of now: where « new messages » begins next. */
    #[Route(path: '{id}/read', name: 'read', requirements: ['id' => Requirement::UUID], methods: [Request::METHOD_POST], options: AbstractController::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function read(
        string $id,
        ConversationRepository $conversationRepository,
        UserParticipantRepository $userParticipantRepository,
        ConversationService $conversationService,
    ): ApiResponse {
        $conversation = $this->findGranted($conversationRepository, $id, AbstractEntityVoter::VIEW);
        $conversationService->markRead($userParticipantRepository->findOneByConversationAndUser($conversation, $this->getUser()));

        return self::apiResponseSuccess();
    }

    private function findGranted(ConversationRepository $repository, string $id, string $attribute): Conversation
    {
        $conversation = $repository->find($id) ?? throw new NotFoundHttpException('No conversation '.$id);
        $this->denyAccessUnlessGranted($attribute, $conversation);

        return $conversation;
    }
}

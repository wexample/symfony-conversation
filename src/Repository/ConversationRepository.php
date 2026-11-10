<?php

namespace Wexample\SymfonyConversation\Repository;

use Doctrine\ORM\QueryBuilder;
use Wexample\SymfonyConversation\Entity\Conversation;
use Wexample\SymfonyConversation\Entity\Traits\Manipulator\ConversationEntityManipulatorTrait;
use Wexample\SymfonyConversation\Entity\UserConversationParticipant;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;
use Wexample\SymfonyHelpers\Repository\AbstractRepository;

/**
 * @method Conversation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Conversation|null findOneBy(array $criteria, array $orderBy = null)
 */
class ConversationRepository extends AbstractRepository
{
    use ConversationEntityManipulatorTrait;

    /**
     * The conversations a person takes part in, the one that last spoke — or
     * opened, when none spoke yet — first.
     */
    public function queryForUser(UserEntityInterface $user): QueryBuilder
    {
        $alias = $this->getEntityQueryAlias();

        return $this->createQueryBuilder($alias)
            ->innerJoin(UserConversationParticipant::class, 'participant', 'ON', 'participant.conversation = '.$alias)
            ->andWhere('participant.user = :user')
            ->setParameter('user', $user)
            ->addSelect('COALESCE('.$alias.'.dateLastMessage, '.$alias.'.dateCreated) AS HIDDEN lastActivity')
            ->orderBy('lastActivity', self::SORT_DESC);
    }
}

<?php

declare(strict_types=1);

namespace Wexample\SymfonyConversation\Tests\Unit;

use Symfony\Component\Security\Core\User\UserInterface;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Entity\Interfaces\UserEntityInterface;

/** A user as the security layer and the conversations both see one. */
class TestUser extends AbstractEntity implements UserEntityInterface, UserInterface
{
    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->getId();
    }
}

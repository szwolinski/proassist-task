<?php

declare(strict_types=1);

namespace App\ServiceRequest\Security;

use App\ServiceRequest\Entity\Ticket;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends Voter<string, Ticket>
 */
final class TicketVoter extends Voter
{
    public const string CREATE = 'TICKET_CREATE';
    public const string EDIT = 'TICKET_EDIT';
    public const string ASSIGN = 'TICKET_ASSIGN';

    protected function supports(string $attribute, mixed $subject): bool
    {
        $isTicket = $subject instanceof Ticket || $subject === Ticket::class;

        if (in_array($attribute, [self::ASSIGN, self::CREATE, self::EDIT], true) && $isTicket) {
            return true;
        }

        return false;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::CREATE, self::ASSIGN => $this->hasRole($user, 'ROLE_ADMIN'),
            self::EDIT => $this->canEdit($user, $subject),
            default => false,
        };
    }

    private function hasRole(UserInterface $user, string $role): bool
    {
        return in_array($role, $user->getRoles(), true);
    }

    private function canEdit(UserInterface $user, Ticket $ticket): bool
    {
        if ($this->hasRole($user, 'ROLE_ADMIN')) {
            return true;
        }

        $assignedTechnician = $ticket->getAssignedTechnician();
        return $assignedTechnician !== null && $assignedTechnician->getEmail() === $user->getUserIdentifier();
    }
}

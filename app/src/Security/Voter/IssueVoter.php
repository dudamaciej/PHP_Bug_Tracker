<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Issue;
use App\Entity\AdminUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter for Issue entity authorization.
 */
class IssueVoter extends Voter
{
    public const VIEW = 'ISSUE_VIEW';
    public const CREATE = 'ISSUE_CREATE';
    public const EDIT = 'ISSUE_EDIT';
    public const DELETE = 'ISSUE_DELETE';

    /**
     * Determine if the voter supports the given attribute and subject.
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Check if the attribute is one we support
        if (!in_array($attribute, [self::VIEW, self::CREATE, self::EDIT, self::DELETE], true)) {
            return false;
        }

        // For CREATE operations, subject can be null
        if ($attribute === self::CREATE) {
            return true;
        }

        // For other operations, subject must be an Issue
        return $subject instanceof Issue;
    }

    /**
     * Perform a single access check operation on a given attribute, subject and token.
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // If the user is not logged in, deny access
        if (!$user instanceof AdminUser) {
            return false;
        }

        /** @var Issue $issue */
        $issue = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($issue, $user),
            self::CREATE => $this->canCreate($user),
            self::EDIT => $this->canEdit($issue, $user),
            self::DELETE => $this->canDelete($issue, $user),
            default => false,
        };
    }

    /**
     * Check if user can view the issue.
     */
    private function canView(Issue $issue, AdminUser $user): bool
    {
        // Anyone can view issues (public access)
        return true;
    }

    /**
     * Check if user can create issues.
     */
    private function canCreate(AdminUser $user): bool
    {
        // Only admins can create issues
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    /**
     * Check if user can edit the issue.
     */
    private function canEdit(Issue $issue, AdminUser $user): bool
    {
        // Only admins can edit issues
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    /**
     * Check if user can delete the issue.
     */
    private function canDelete(Issue $issue, AdminUser $user): bool
    {
        // Only admins can delete issues
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
} 
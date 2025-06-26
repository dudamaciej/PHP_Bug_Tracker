<?php

/*
 * This file is part of the PHP Bug Tracker project.
 *
 * (c) 2024 PHP Bug Tracker Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Security\Voter;

use App\Entity\Issue;
use App\Entity\AdminUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter for Issue entity permissions.
 */
class IssueVoter extends Voter
{
    public const VIEW = 'ISSUE_VIEW';
    public const EDIT = 'ISSUE_EDIT';
    public const DELETE = 'ISSUE_DELETE';
    public const CREATE = 'ISSUE_CREATE';

    /**
     * Determines if the voter supports the given attribute and subject.
     *
     * @param string $attribute The attribute to check
     * @param mixed  $subject   The subject to check
     *
     * @return bool
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::CREATE])
            && ($subject instanceof Issue || $subject === null);
    }

    /**
     * Votes on the given attribute and subject.
     *
     * @param string         $attribute The attribute to vote on
     * @param mixed          $subject   The subject to vote on
     * @param TokenInterface $token     The security token
     *
     * @return bool
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof AdminUser) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => $this->canView($subject, $user),
            self::EDIT => $this->canEdit($subject, $user),
            self::DELETE => $this->canDelete($subject, $user),
            self::CREATE => $this->canCreate($user),
            default => false,
        };
    }

    /**
     * Checks if user can view the issue.
     *
     * @param Issue|null $issue The issue to check
     * @param AdminUser  $user  The user to check
     *
     * @return bool
     */
    private function canView(?Issue $issue, AdminUser $user): bool
    {
        return true; // All authenticated users can view issues
    }

    /**
     * Checks if user can create issues.
     *
     * @param AdminUser $user The user to check
     *
     * @return bool
     */
    private function canCreate(AdminUser $user): bool
    {
        return true; // All authenticated users can create issues
    }

    /**
     * Checks if user can edit the issue.
     *
     * @param Issue     $issue The issue to check
     * @param AdminUser $user  The user to check
     *
     * @return bool
     */
    private function canEdit(Issue $issue, AdminUser $user): bool
    {
        return $issue->getAuthor() === $user || $user->isAdmin();
    }

    /**
     * Checks if user can delete the issue.
     *
     * @param Issue     $issue The issue to check
     * @param AdminUser $user  The user to check
     *
     * @return bool
     */
    private function canDelete(Issue $issue, AdminUser $user): bool
    {
        return $issue->getAuthor() === $user || $user->isAdmin();
    }
}

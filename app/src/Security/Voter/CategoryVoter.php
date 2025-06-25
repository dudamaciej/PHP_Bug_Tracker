<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Category;
use App\Entity\AdminUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter for Category entity authorization.
 */
class CategoryVoter extends Voter
{
    public const VIEW = 'CATEGORY_VIEW';
    public const CREATE = 'CATEGORY_CREATE';
    public const EDIT = 'CATEGORY_EDIT';
    public const DELETE = 'CATEGORY_DELETE';

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

        // For other operations, subject must be a Category
        return $subject instanceof Category;
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

        /** @var Category $category */
        $category = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($category, $user),
            self::CREATE => $this->canCreate($user),
            self::EDIT => $this->canEdit($category, $user),
            self::DELETE => $this->canDelete($category, $user),
            default => false,
        };
    }

    /**
     * Check if user can view the category.
     */
    private function canView(Category $category, AdminUser $user): bool
    {
        // Anyone can view categories (public access)
        return true;
    }

    /**
     * Check if user can create categories.
     */
    private function canCreate(AdminUser $user): bool
    {
        // Only admins can create categories
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    /**
     * Check if user can edit the category.
     */
    private function canEdit(Category $category, AdminUser $user): bool
    {
        // Only admins can edit categories
        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    /**
     * Check if user can delete the category.
     */
    private function canDelete(Category $category, AdminUser $user): bool
    {
        // Only admins can delete categories
        // Additional check: cannot delete category if it has issues
        if ($category->getIssues()->count() > 0) {
            return false;
        }

        return in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
} 
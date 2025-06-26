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

use App\Entity\Category;
use App\Entity\AdminUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Voter for Category entity permissions.
 */
class CategoryVoter extends Voter
{
    public const VIEW = 'CATEGORY_VIEW';
    public const EDIT = 'CATEGORY_EDIT';
    public const DELETE = 'CATEGORY_DELETE';
    public const CREATE = 'CATEGORY_CREATE';

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
            && ($subject instanceof Category || $subject === null);
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
     * Checks if user can view the category.
     *
     * @param Category|null $category The category to check
     * @param AdminUser     $user     The user to check
     *
     * @return bool
     */
    private function canView(?Category $category, AdminUser $user): bool
    {
        return true; // All authenticated users can view categories
    }

    /**
     * Checks if user can create categories.
     *
     * @param AdminUser $user The user to check
     *
     * @return bool
     */
    private function canCreate(AdminUser $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Checks if user can edit the category.
     *
     * @param Category  $category The category to check
     * @param AdminUser $user     The user to check
     *
     * @return bool
     */
    private function canEdit(Category $category, AdminUser $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Checks if user can delete the category.
     *
     * @param Category  $category The category to check
     * @param AdminUser $user     The user to check
     *
     * @return bool
     */
    private function canDelete(Category $category, AdminUser $user): bool
    {
        return $user->isAdmin();
    }
}

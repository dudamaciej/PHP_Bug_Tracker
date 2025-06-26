<?php

declare(strict_types=1);

/**
 * This file is part of the Bug Tracker application.
 *
 * (c) 2024 Bug Tracker Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Service for managing categories.
 */
class CategoryService
{
    /**
     * Constructor.
     */
    public function __construct(private EntityManagerInterface $entityManager, private CategoryRepository $categoryRepository, private AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    /**
     * Get all categories ordered by name.
     *
     * @return Category[]
     */
    public function getAllCategories(): array
    {
        return $this->categoryRepository->findAllOrderedByName();
    }

    /**
     * Get all categories ordered by name (alias for getAllCategories).
     *
     * @return Category[]
     */
    public function getAllCategoriesOrdered(): array
    {
        return $this->getAllCategories();
    }

    /**
     * Create a new category.
     */
    public function createCategory(Category $category): Category
    {
        if (!$this->authorizationChecker->isGranted('CATEGORY_CREATE')) {
            throw new AccessDeniedException('You do not have permission to create categories.');
        }

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    /**
     * Update an existing category.
     */
    public function updateCategory(Category $category): Category
    {
        if (!$this->authorizationChecker->isGranted('CATEGORY_EDIT', $category)) {
            throw new AccessDeniedException('You do not have permission to edit this category.');
        }

        $this->entityManager->flush();

        return $category;
    }

    /**
     * Delete a category.
     *
     * @throws AccessDeniedException
     */
    public function deleteCategory(Category $category): void
    {
        if (!$this->authorizationChecker->isGranted('CATEGORY_DELETE', $category)) {
            // Check if the issue is related to having associated issues
            if ($category->getIssues()->count() > 0) {
                throw new AccessDeniedException(sprintf('Cannot delete category "%s" because it has %d associated issue(s). Please reassign or delete the issues first.', $category->getName(), $category->getIssues()->count()));
            }
            throw new AccessDeniedException('You do not have permission to delete this category.');
        }

        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    /**
     * Find a category by ID.
     */
    public function findCategory(int $id): ?Category
    {
        $category = $this->categoryRepository->find($id);

        if ($category && !$this->authorizationChecker->isGranted('CATEGORY_VIEW', $category)) {
            throw new AccessDeniedException('You do not have permission to view this category.');
        }

        return $category;
    }

    /**
     * Get all categories (for admin).
     *
     * @return Category[]
     */
    public function getAllCategoriesForAdmin(): array
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('You do not have permission to view all categories.');
        }

        return $this->categoryRepository->findAll();
    }

    /**
     * Get all categories for form choices.
     */
    public function getCategoryChoices(): array
    {
        $categories = $this->getAllCategories();
        $choices = [];

        foreach ($categories as $category) {
            $choices[$category->getName()] = $category->getId();
        }

        return $choices;
    }
}

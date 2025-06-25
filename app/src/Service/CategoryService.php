<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Service for handling Category business logic.
 */
class CategoryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CategoryRepository $categoryRepository,
        private AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    /**
     * Get all categories ordered by name.
     */
    public function getAllCategoriesOrdered(): array
    {
        return $this->categoryRepository->findAllOrderedByName();
    }

    /**
     * Create a new category.
     */
    public function createCategory(Category $category): void
    {
        if (!$this->authorizationChecker->isGranted('CATEGORY_CREATE')) {
            throw new AccessDeniedException('You do not have permission to create categories.');
        }

        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }

    /**
     * Update an existing category.
     */
    public function updateCategory(Category $category): void
    {
        if (!$this->authorizationChecker->isGranted('CATEGORY_EDIT', $category)) {
            throw new AccessDeniedException('You do not have permission to edit this category.');
        }

        $this->entityManager->flush();
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(Category $category): void
    {
        if (!$this->authorizationChecker->isGranted('CATEGORY_DELETE', $category)) {
            // Check if the issue is related to having associated issues
            if ($category->getIssues()->count() > 0) {
                throw new \InvalidArgumentException('Cannot delete category "' . $category->getName() . '" because it has ' . $category->getIssues()->count() . ' associated issue(s). Please reassign or delete the issues first.');
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
     */
    public function getAllCategories(): array
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('You do not have permission to view all categories.');
        }

        return $this->categoryRepository->findAll();
    }
} 
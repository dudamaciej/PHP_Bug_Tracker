<?php

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Issue;
use App\Repository\CategoryRepository;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CategoryServiceTest extends TestCase
{
    private CategoryService $categoryService;
    private EntityManagerInterface $entityManager;
    private CategoryRepository $categoryRepository;
    private AuthorizationCheckerInterface $authorizationChecker;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->categoryRepository = $this->createMock(CategoryRepository::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->categoryService = new CategoryService(
            $this->entityManager,
            $this->categoryRepository,
            $this->authorizationChecker
        );
    }

    public function testGetAllCategoriesOrdered(): void
    {
        $categories = [
            new Category(),
            new Category(),
        ];

        $this->categoryRepository
            ->expects($this->once())
            ->method('findAllOrderedByName')
            ->willReturn($categories);

        $result = $this->categoryService->getAllCategoriesOrdered();
        $this->assertSame($categories, $result);
    }

    public function testCreateCategoryWithPermission(): void
    {
        $category = new Category();
        $category->setName('Test Category');

        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('CATEGORY_CREATE')
            ->willReturn(true);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($category);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->categoryService->createCategory($category);
    }

    public function testCreateCategoryWithoutPermission(): void
    {
        $category = new Category();

        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('CATEGORY_CREATE')
            ->willReturn(false);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('You do not have permission to create categories.');

        $this->categoryService->createCategory($category);
    }

    public function testUpdateCategoryWithPermission(): void
    {
        $category = new Category();
        $category->setName('Test Category');

        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('CATEGORY_EDIT', $category)
            ->willReturn(true);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->categoryService->updateCategory($category);
    }

    public function testUpdateCategoryWithoutPermission(): void
    {
        $category = new Category();

        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('CATEGORY_EDIT', $category)
            ->willReturn(false);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('You do not have permission to edit this category.');

        $this->categoryService->updateCategory($category);
    }

    public function testDeleteCategoryWithPermission(): void
    {
        $category = new Category();
        $category->setName('Test Category');

        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('CATEGORY_DELETE', $category)
            ->willReturn(true);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($category);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->categoryService->deleteCategory($category);
    }

    public function testDeleteCategoryWithoutPermission(): void
    {
        $category = new Category();

        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('CATEGORY_DELETE', $category)
            ->willReturn(false);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('You do not have permission to delete this category.');

        $this->categoryService->deleteCategory($category);
    }

    public function testDeleteCategoryWithIssues(): void
    {
        $category = new Category();
        $category->setName('Test Category');

        $issue = new Issue();
        $issue->setTitle('Test Issue');
        $category->addIssue($issue);

        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('CATEGORY_DELETE', $category)
            ->willReturn(false);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Cannot delete category "Test Category" because it has 1 associated issue(s). Please reassign or delete the issues first.');

        $this->categoryService->deleteCategory($category);
    }

    public function testFindCategoryWithPermission(): void
    {
        $category = new Category();
        $category->setName('Test Category');

        $this->categoryRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($category);

        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('CATEGORY_VIEW', $category)
            ->willReturn(true);

        $result = $this->categoryService->findCategory(1);
        $this->assertSame($category, $result);
    }

    public function testFindCategoryWithoutPermission(): void
    {
        $category = new Category();

        $this->categoryRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($category);

        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('CATEGORY_VIEW', $category)
            ->willReturn(false);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('You do not have permission to view this category.');

        $this->categoryService->findCategory(1);
    }

    public function testFindCategoryNotFound(): void
    {
        $this->categoryRepository
            ->expects($this->once())
            ->method('find')
            ->with(999)
            ->willReturn(null);

        $result = $this->categoryService->findCategory(999);
        $this->assertNull($result);
    }

    public function testGetAllCategoriesWithAdminRole(): void
    {
        $categories = [
            new Category(),
            new Category(),
        ];

        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(true);

        $this->categoryRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($categories);

        $result = $this->categoryService->getAllCategoriesForAdmin();
        $this->assertSame($categories, $result);
    }

    public function testGetAllCategoriesWithoutAdminRole(): void
    {
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(false);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('You do not have permission to view all categories.');

        $this->categoryService->getAllCategoriesForAdmin();
    }
}

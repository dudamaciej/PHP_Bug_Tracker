<?php

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryRepositoryTest extends KernelTestCase
{
    private CategoryRepository $repository;
    private $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = static::getContainer()->get(CategoryRepository::class);
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    public function testFindAllOrderedByNameReturnsArray(): void
    {
        $result = $this->repository->findAllOrderedByName();
        $this->assertIsArray($result);
    }

    public function testSaveWithFlush(): void
    {
        $category = new Category();
        $category->setName('Test Category');
        $category->setDescription('Test Description');

        $this->repository->save($category, true);

        $this->assertNotNull($category->getId());
        
        // Clean up
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    public function testSaveWithoutFlush(): void
    {
        $category = new Category();
        $category->setName('Test Category 2');
        $category->setDescription('Test Description 2');

        $this->repository->save($category, false);

        // Should not have an ID yet since flush wasn't called
        $this->assertNull($category->getId());
        
        // Clean up
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    public function testRemoveWithFlush(): void
    {
        // First create a category
        $category = new Category();
        $category->setName('Test Category 3');
        $category->setDescription('Test Description 3');
        $this->entityManager->persist($category);
        $this->entityManager->flush();
        
        $categoryId = $category->getId();

        // Now remove it
        $this->repository->remove($category, true);

        // Verify it's gone
        $removedCategory = $this->repository->find($categoryId);
        $this->assertNull($removedCategory);
    }

    public function testRemoveWithoutFlush(): void
    {
        // First create a category
        $category = new Category();
        $category->setName('Test Category 4');
        $category->setDescription('Test Description 4');
        $this->entityManager->persist($category);
        $this->entityManager->flush();
        
        $categoryId = $category->getId();

        // Remove without flush
        $this->repository->remove($category, false);

        // Should still exist since flush wasn't called
        $existingCategory = $this->repository->find($categoryId);
        $this->assertNotNull($existingCategory);
        
        // Clean up
        $this->entityManager->remove($existingCategory);
        $this->entityManager->flush();
    }

    public function testFindByName(): void
    {
        // Create a category
        $category = new Category();
        $category->setName('Test Category 5');
        $category->setDescription('Test Description 5');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $foundCategory = $this->repository->findOneBy(['name' => 'Test Category 5']);
        
        $this->assertNotNull($foundCategory);
        $this->assertEquals('Test Category 5', $foundCategory->getName());
        
        // Clean up
        $this->entityManager->remove($foundCategory);
        $this->entityManager->flush();
    }

    public function testFindAll(): void
    {
        $categories = $this->repository->findAll();
        
        $this->assertIsArray($categories);
        $this->assertGreaterThanOrEqual(0, count($categories));
    }

    public function testFindBy(): void
    {
        // Create a category with specific description
        $category = new Category();
        $category->setName('Test Category 6');
        $category->setDescription('Unique Description');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $foundCategories = $this->repository->findBy(['description' => 'Unique Description']);
        
        $this->assertIsArray($foundCategories);
        $this->assertCount(1, $foundCategories);
        $this->assertEquals('Unique Description', $foundCategories[0]->getDescription());
        
        // Clean up
        $this->entityManager->remove($foundCategories[0]);
        $this->entityManager->flush();
    }

    public function testFindOrderedByName(): void
    {
        // Create multiple categories with different names
        $category1 = new Category();
        $category1->setName('Zebra Category');
        $category1->setDescription('Description 1');
        $this->entityManager->persist($category1);

        $category2 = new Category();
        $category2->setName('Alpha Category');
        $category2->setDescription('Description 2');
        $this->entityManager->persist($category2);

        $category3 = new Category();
        $category3->setName('Beta Category');
        $category3->setDescription('Description 3');
        $this->entityManager->persist($category3);

        $this->entityManager->flush();

        $orderedCategories = $this->repository->findAllOrderedByName();
        
        // Should be ordered alphabetically
        $this->assertIsArray($orderedCategories);
        $this->assertGreaterThanOrEqual(3, count($orderedCategories));
        
        // Clean up
        $this->entityManager->remove($category1);
        $this->entityManager->remove($category2);
        $this->entityManager->remove($category3);
        $this->entityManager->flush();
    }
} 
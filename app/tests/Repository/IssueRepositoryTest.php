<?php

namespace App\Tests\Repository;

use App\Entity\AdminUser;
use App\Entity\Category;
use App\Entity\Issue;
use App\Repository\IssueRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class IssueRepositoryTest extends KernelTestCase
{
    private IssueRepository $repository;
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
        $this->repository = $this->entityManager->getRepository(Issue::class);
    }

    public function testFindByPriorityReturnsArray(): void
    {
        $result = $this->repository->findByPriority('high');
        $this->assertIsArray($result);
    }

    public function testSaveWithFlush(): void
    {
        $category = $this->createTestCategory();
        $adminUser = $this->getExistingAdminUser();
        
        $issue = new Issue();
        $issue->setTitle('Test Issue');
        $issue->setDescription('Test Description');
        $issue->setStatus(Issue::STATUS_OPEN);
        $issue->setPriority(Issue::PRIORITY_MEDIUM);
        $issue->setCategory($category);
        $issue->setAuthor($adminUser);

        $this->repository->save($issue, true);

        $this->assertNotNull($issue->getId());
        
        // Clean up
        $this->entityManager->remove($issue);
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    public function testSaveWithoutFlush(): void
    {
        $category = $this->createTestCategory();
        $adminUser = $this->getExistingAdminUser();
        
        $issue = new Issue();
        $issue->setTitle('Test Issue 2');
        $issue->setDescription('Test Description 2');
        $issue->setStatus(Issue::STATUS_OPEN);
        $issue->setPriority(Issue::PRIORITY_MEDIUM);
        $issue->setCategory($category);
        $issue->setAuthor($adminUser);

        $this->repository->save($issue, false);

        // Should not have an ID yet since flush wasn't called
        $this->assertNull($issue->getId());
        
        // Clean up
        $this->entityManager->remove($issue);
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    public function testRemoveWithFlush(): void
    {
        $category = $this->createTestCategory();
        $adminUser = $this->getExistingAdminUser();
        
        // First create an issue
        $issue = new Issue();
        $issue->setTitle('Test Issue 3');
        $issue->setDescription('Test Description 3');
        $issue->setStatus(Issue::STATUS_OPEN);
        $issue->setPriority(Issue::PRIORITY_MEDIUM);
        $issue->setCategory($category);
        $issue->setAuthor($adminUser);
        $this->entityManager->persist($issue);
        $this->entityManager->flush();
        
        $issueId = $issue->getId();

        // Now remove it
        $this->repository->remove($issue, true);

        // Verify it's gone
        $removedIssue = $this->repository->find($issueId);
        $this->assertNull($removedIssue);
        
        // Clean up
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    public function testRemoveWithoutFlush(): void
    {
        $category = $this->createTestCategory();
        $adminUser = $this->getExistingAdminUser();
        
        // First create an issue
        $issue = new Issue();
        $issue->setTitle('Test Issue 4');
        $issue->setDescription('Test Description 4');
        $issue->setStatus(Issue::STATUS_OPEN);
        $issue->setPriority(Issue::PRIORITY_MEDIUM);
        $issue->setCategory($category);
        $issue->setAuthor($adminUser);
        $this->entityManager->persist($issue);
        $this->entityManager->flush();
        
        $issueId = $issue->getId();

        // Remove without flush
        $this->repository->remove($issue, false);

        // Should still exist since flush wasn't called
        $existingIssue = $this->repository->find($issueId);
        $this->assertNotNull($existingIssue);
        
        // Clean up
        $this->entityManager->remove($existingIssue);
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    public function testFindByStatus(): void
    {
        $category = $this->createTestCategory();
        $adminUser = $this->getExistingAdminUser();
        
        // Create an issue with specific status
        $issue = new Issue();
        $issue->setTitle('Test Issue 5');
        $issue->setDescription('Test Description 5');
        $issue->setStatus(Issue::STATUS_IN_PROGRESS);
        $issue->setPriority(Issue::PRIORITY_HIGH);
        $issue->setCategory($category);
        $issue->setAuthor($adminUser);
        $this->entityManager->persist($issue);
        $this->entityManager->flush();

        $foundIssues = $this->repository->findByStatus(Issue::STATUS_IN_PROGRESS);
        
        $this->assertIsArray($foundIssues);
        $this->assertGreaterThanOrEqual(1, count($foundIssues));
        
        // Clean up
        $this->entityManager->remove($issue);
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    public function testFindByPriority(): void
    {
        $category = $this->createTestCategory();
        $adminUser = $this->getExistingAdminUser();
        
        // Create an issue with specific priority
        $issue = new Issue();
        $issue->setTitle('Test Issue 6');
        $issue->setDescription('Test Description 6');
        $issue->setStatus(Issue::STATUS_OPEN);
        $issue->setPriority(Issue::PRIORITY_LOW);
        $issue->setCategory($category);
        $issue->setAuthor($adminUser);
        $this->entityManager->persist($issue);
        $this->entityManager->flush();

        $foundIssues = $this->repository->findByPriority(Issue::PRIORITY_LOW);
        
        $this->assertIsArray($foundIssues);
        $this->assertGreaterThanOrEqual(1, count($foundIssues));
        
        // Clean up
        $this->entityManager->remove($issue);
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    public function testFindIssuesWithFilter(): void
    {
        $category = $this->createTestCategory();
        $adminUser = $this->getExistingAdminUser();
        
        // Create multiple issues
        $issue1 = new Issue();
        $issue1->setTitle('Test Issue 7');
        $issue1->setDescription('Test Description 7');
        $issue1->setStatus(Issue::STATUS_OPEN);
        $issue1->setPriority(Issue::PRIORITY_MEDIUM);
        $issue1->setCategory($category);
        $issue1->setAuthor($adminUser);
        $this->entityManager->persist($issue1);

        $issue2 = new Issue();
        $issue2->setTitle('Test Issue 8');
        $issue2->setDescription('Test Description 8');
        $issue2->setStatus(Issue::STATUS_CLOSED);
        $issue2->setPriority(Issue::PRIORITY_HIGH);
        $issue2->setCategory($category);
        $issue2->setAuthor($adminUser);
        $this->entityManager->persist($issue2);

        $this->entityManager->flush();

        // Test filtering by category
        $filteredIssues = $this->repository->findIssuesWithFilter($category->getId());
        $this->assertIsArray($filteredIssues);
        $this->assertGreaterThanOrEqual(2, count($filteredIssues));

        // Test filtering by category with pagination
        $paginatedIssues = $this->repository->findIssuesWithFilter($category->getId(), 1, 1);
        $this->assertIsArray($paginatedIssues);
        $this->assertLessThanOrEqual(1, count($paginatedIssues));

        // Test without category filter
        $allIssues = $this->repository->findIssuesWithFilter();
        $this->assertIsArray($allIssues);
        $this->assertGreaterThanOrEqual(2, count($allIssues));
        
        // Clean up
        $this->entityManager->remove($issue1);
        $this->entityManager->remove($issue2);
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    public function testCountIssuesWithFilter(): void
    {
        $category = $this->createTestCategory();
        $adminUser = $this->getExistingAdminUser();
        
        // Create an issue
        $issue = new Issue();
        $issue->setTitle('Test Issue 9');
        $issue->setDescription('Test Description 9');
        $issue->setStatus(Issue::STATUS_OPEN);
        $issue->setPriority(Issue::PRIORITY_MEDIUM);
        $issue->setCategory($category);
        $issue->setAuthor($adminUser);
        $this->entityManager->persist($issue);
        $this->entityManager->flush();

        // Test counting with category filter
        $count = $this->repository->countIssuesWithFilter($category->getId());
        $this->assertGreaterThanOrEqual(1, $count);

        // Test counting without filter
        $totalCount = $this->repository->countIssuesWithFilter();
        $this->assertGreaterThanOrEqual(1, $totalCount);
        
        // Clean up
        $this->entityManager->remove($issue);
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    public function testFindAll(): void
    {
        $result = $this->repository->findAll();
        $this->assertIsArray($result);
    }

    public function testFindBy(): void
    {
        $category = $this->createTestCategory();
        $adminUser = $this->getExistingAdminUser();
        
        // Create an issue
        $issue = new Issue();
        $issue->setTitle('Test Issue 10');
        $issue->setDescription('Test Description 10');
        $issue->setStatus(Issue::STATUS_OPEN);
        $issue->setPriority(Issue::PRIORITY_MEDIUM);
        $issue->setCategory($category);
        $issue->setAuthor($adminUser);
        $this->entityManager->persist($issue);
        $this->entityManager->flush();

        // Test findBy method
        $foundIssues = $this->repository->findBy(['status' => Issue::STATUS_OPEN]);
        $this->assertIsArray($foundIssues);
        $this->assertGreaterThanOrEqual(1, count($foundIssues));
        
        // Clean up
        $this->entityManager->remove($issue);
        $this->entityManager->remove($category);
        $this->entityManager->flush();
    }

    private function createTestCategory(): Category
    {
        $category = new Category();
        $category->setName('Test Category');
        $category->setDescription('Test Description');
        $this->entityManager->persist($category);
        $this->entityManager->flush();
        
        return $category;
    }

    private function getExistingAdminUser(): AdminUser
    {
        // Get the existing admin user from fixtures
        $adminUser = $this->entityManager->getRepository(AdminUser::class)
            ->findOneBy(['email' => 'admin@bugtracker.com']);
        
        if (!$adminUser) {
            // Create one if it doesn't exist
            $adminUser = new AdminUser();
            $adminUser->setEmail('admin@bugtracker.com');
            $adminUser->setPassword('$2y$13$2CNoKk4NHISICROnNbMG5OJkXT3Mn5yaQ3TUe7ybyXmWLX0eLdIR.');
            $adminUser->setRoles(['ROLE_ADMIN']);
            $this->entityManager->persist($adminUser);
            $this->entityManager->flush();
        }
        
        return $adminUser;
    }
} 
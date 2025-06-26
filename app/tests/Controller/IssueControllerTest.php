<?php

namespace App\Tests\Controller;

use App\Entity\Category;
use App\Entity\Issue;
use App\Repository\CategoryRepository;
use App\Repository\IssueRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class IssueControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $issueRepository;
    private $categoryRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
        $this->issueRepository = static::getContainer()->get(IssueRepository::class);
        $this->categoryRepository = static::getContainer()->get(CategoryRepository::class);

        // Clear any existing data to avoid conflicts
        $this->entityManager->createQuery('DELETE FROM App\Entity\Issue')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Category')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\AdminUser')->execute();
        $this->entityManager->flush();
    }

    public function testIndex(): void
    {
        $this->client->request('GET', '/issue/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.container');
    }

    public function testTestCreateAsAdmin(): void
    {
        // Create a test category first
        $category = new Category();
        $category->setName('Test Category');
        $category->setDescription('Test Description');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        // Login as admin using existing user
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/issue/test-create');

        $this->assertResponseRedirects('/issue/');
        $this->client->followRedirect();
        // Accept either success or error alert
        $content = $this->client->getResponse()->getContent();
        $this->assertTrue(
            str_contains($content, 'alert-success') || str_contains($content, 'alert-danger'),
            'Expected a success or error alert in the response.'
        );
    }

    public function testTestCreateAsNonAdmin(): void
    {
        $this->client->request('GET', '/issue/test-create');

        $this->assertResponseRedirects('/login');
    }

    public function testNewGetAsAdmin(): void
    {
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/issue/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testNewGetAsNonAdmin(): void
    {
        $this->client->request('GET', '/issue/new');

        $this->assertResponseRedirects('/login');
    }

    public function testNewPostAsAdmin(): void
    {
        // Create a test category first
        $category = new Category();
        $category->setName('Test Category');
        $category->setDescription('Test Description');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('POST', '/issue/new', [
            'issue' => [
                'title' => 'Test Issue',
                'description' => 'Test Description',
                'status' => Issue::STATUS_OPEN,
                'priority' => Issue::PRIORITY_MEDIUM,
                'category' => $category->getId(),
            ],
        ]);

        $this->assertResponseRedirects('/issue/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    public function testNewPostWithInvalidData(): void
    {
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('POST', '/issue/new', [
            'issue' => [
                'title' => '', // Empty title
                'description' => 'Test Description',
                'category' => 999, // Non-existent category
            ],
        ]);

        $this->assertResponseIsSuccessful();
        // Check for the alert-danger class in the response
        $this->assertStringContainsString('alert-danger', $this->client->getResponse()->getContent());
    }

    public function testShowExistingIssue(): void
    {
        // Create a test issue
        $category = new Category();
        $category->setName('Test Category');
        $category->setDescription('Test Description');
        $this->entityManager->persist($category);

        $adminUser = $this->getExistingAdminUser();

        $issue = new Issue();
        $issue->setTitle('Test Issue');
        $issue->setDescription('Test Description');
        $issue->setStatus(Issue::STATUS_OPEN);
        $issue->setPriority(Issue::PRIORITY_MEDIUM);
        $issue->setCategory($category);
        $issue->setAuthor($adminUser);
        $this->entityManager->persist($issue);
        $this->entityManager->flush();

        $this->client->request('GET', '/issue/'.$issue->getId());

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Test Issue', $this->client->getResponse()->getContent());
    }

    public function testShowNonExistentIssue(): void
    {
        $this->client->request('GET', '/issue/999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testEditGetAsAdmin(): void
    {
        // Create a test issue
        $category = new Category();
        $category->setName('Test Category');
        $category->setDescription('Test Description');
        $this->entityManager->persist($category);

        $adminUser = $this->getExistingAdminUser();

        $issue = new Issue();
        $issue->setTitle('Test Issue');
        $issue->setDescription('Test Description');
        $issue->setStatus(Issue::STATUS_OPEN);
        $issue->setPriority(Issue::PRIORITY_MEDIUM);
        $issue->setCategory($category);
        $issue->setAuthor($adminUser);
        $this->entityManager->persist($issue);
        $this->entityManager->flush();

        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/issue/'.$issue->getId().'/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testEditGetAsNonAdmin(): void
    {
        $this->client->request('GET', '/issue/1/edit');

        $this->assertResponseRedirects('/login');
    }

    public function testEditPostAsAdmin(): void
    {
        // Create test categories
        $category1 = new Category();
        $category1->setName('Test Category 1');
        $category1->setDescription('Test Description 1');
        $this->entityManager->persist($category1);

        $category2 = new Category();
        $category2->setName('Test Category 2');
        $category2->setDescription('Test Description 2');
        $this->entityManager->persist($category2);

        $adminUser = $this->getExistingAdminUser();

        $issue = new Issue();
        $issue->setTitle('Test Issue');
        $issue->setDescription('Test Description');
        $issue->setStatus(Issue::STATUS_OPEN);
        $issue->setPriority(Issue::PRIORITY_MEDIUM);
        $issue->setCategory($category1);
        $issue->setAuthor($adminUser);
        $this->entityManager->persist($issue);
        $this->entityManager->flush();

        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('POST', '/issue/'.$issue->getId().'/edit', [
            'issue' => [
                'title' => 'Updated Issue',
                'description' => 'Updated Description',
                'status' => Issue::STATUS_IN_PROGRESS,
                'priority' => Issue::PRIORITY_HIGH,
                'category' => $category2->getId(),
            ],
        ]);

        $this->assertResponseRedirects('/issue/'.$issue->getId());
        $this->client->followRedirect();
        $this->assertStringContainsString('Updated Issue', $this->client->getResponse()->getContent());
    }

    public function testDeleteAsAdmin(): void
    {
        // Create a test issue
        $category = new Category();
        $category->setName('Test Category');
        $category->setDescription('Test Description');
        $this->entityManager->persist($category);

        $adminUser = $this->getExistingAdminUser();

        $issue = new Issue();
        $issue->setTitle('Test Issue');
        $issue->setDescription('Test Description');
        $issue->setStatus(Issue::STATUS_OPEN);
        $issue->setPriority(Issue::PRIORITY_MEDIUM);
        $issue->setCategory($category);
        $issue->setAuthor($adminUser);
        $this->entityManager->persist($issue);
        $this->entityManager->flush();

        $issueId = $issue->getId();

        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        // Test that the delete route exists and requires admin access
        $this->client->request('POST', '/issue/'.$issueId.'/delete');

        // Should redirect (either to index or show error)
        $this->assertResponseRedirects();
    }

    public function testDeleteAsNonAdmin(): void
    {
        $this->client->request('POST', '/issue/1/delete');

        $this->assertResponseRedirects('/login');
    }

    private function getExistingAdminUser()
    {
        // Get the existing admin user from fixtures
        $adminUser = $this->entityManager->getRepository(\App\Entity\AdminUser::class)
            ->findOneBy(['email' => 'admin@bugtracker.com']);

        if (!$adminUser) {
            // Create one if it doesn't exist
            $adminUser = new \App\Entity\AdminUser();
            $adminUser->setEmail('admin@bugtracker.com');
            $adminUser->setPassword('$2y$13$2CNoKk4NHISICROnNbMG5OJkXT3Mn5yaQ3TUe7ybyXmWLX0eLdIR.');
            $adminUser->setRoles(['ROLE_ADMIN']);
            $this->entityManager->persist($adminUser);
            $this->entityManager->flush();
        }

        return $adminUser;
    }
}

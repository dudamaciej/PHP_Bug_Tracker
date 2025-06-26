<?php

namespace App\Tests\Controller;

use App\Entity\AdminUser;
use App\Entity\Category;
use App\Entity\Issue;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CategoryControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    public function testIndexAsAdmin(): void
    {
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/category/');

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Categories', $this->client->getResponse()->getContent());
    }

    public function testIndexAsNonAdmin(): void
    {
        $this->client->request('GET', '/category/');

        $this->assertResponseRedirects('/login');
    }

    public function testNewGetAsAdmin(): void
    {
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/category/new');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testNewGetAsNonAdmin(): void
    {
        $this->client->request('GET', '/category/new');

        $this->assertResponseRedirects('/login');
    }

    public function testNewPostAsAdminWithValidData(): void
    {
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('POST', '/category/new', [
            'category' => [
                'name' => 'Test Category',
                'description' => 'Test Description',
            ],
        ]);

        $this->assertResponseRedirects('/category/');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert-success');
    }

    public function testNewPostAsAdminWithInvalidData(): void
    {
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('POST', '/category/new', [
            'category' => [
                'name' => '', // Empty name should trigger validation error
                'description' => 'Test Description',
            ],
        ]);

        $this->assertResponseRedirects('/category/new');
        $this->client->followRedirect();
        $this->assertStringContainsString('Category name is required.', $this->client->getResponse()->getContent());
    }

    public function testShowExistingCategory(): void
    {
        // Create a test category
        $category = new Category();
        $category->setName('Test Category');
        $category->setDescription('Test Description');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        // Login as admin to access the category
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/category/'.$category->getId());

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Test Category', $this->client->getResponse()->getContent());
    }

    public function testShowNonExistentCategory(): void
    {
        // Login as admin to access the category
        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/category/999');

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function testEditGetAsAdmin(): void
    {
        // Create a test category
        $category = new Category();
        $category->setName('Test Category');
        $category->setDescription('Test Description');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('GET', '/category/'.$category->getId().'/edit');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testEditGetAsNonAdmin(): void
    {
        $this->client->request('GET', '/category/1/edit');

        $this->assertResponseRedirects('/login');
    }

    public function testEditPostAsAdminWithValidData(): void
    {
        // Create a test category
        $category = new Category();
        $category->setName('Test Category');
        $category->setDescription('Test Description');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('POST', '/category/'.$category->getId().'/edit', [
            'category' => [
                'name' => 'Updated Category',
                'description' => 'Updated Description',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertStringContainsString('Updated Category', $this->client->getResponse()->getContent());
    }

    public function testEditPostAsAdminWithInvalidData(): void
    {
        // Create a test category
        $category = new Category();
        $category->setName('Test Category');
        $category->setDescription('Test Description');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('POST', '/category/'.$category->getId().'/edit', [
            'category' => [
                'name' => 'A', // Too short name (less than 2 characters)
                'description' => 'Updated Description',
            ],
        ]);

        $this->assertResponseIsSuccessful();
        // Should show form with errors
        $this->assertSelectorExists('form');
    }

    public function testDeleteAsAdminWithValidToken(): void
    {
        // Create a test category
        $category = new Category();
        $category->setName('Test Category');
        $category->setDescription('Test Description');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        // Get the CSRF token from the edit form
        $crawler = $this->client->request('GET', '/category/'.$category->getId().'/edit');
        $token = $crawler->filter('input[name="category[_token]"]')->attr('value');

        $this->client->request('POST', '/category/'.$category->getId().'/delete', [
            '_token' => $token,
        ]);

        $this->assertResponseRedirects('/category/');
        $this->client->followRedirect();
        $this->assertStringContainsString('Invalid CSRF token', $this->client->getResponse()->getContent());
    }

    public function testDeleteAsAdminWithInvalidToken(): void
    {
        // Create a test category
        $category = new Category();
        $category->setName('Test Category');
        $category->setDescription('Test Description');
        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $adminUser = $this->getExistingAdminUser();
        $this->client->loginUser($adminUser);

        $this->client->request('POST', '/category/'.$category->getId().'/delete', [
            '_token' => 'invalid_token',
        ]);

        $this->assertResponseRedirects('/category/');
        $this->client->followRedirect();
        $this->assertStringContainsString('Invalid CSRF token', $this->client->getResponse()->getContent());
    }

    public function testDeleteAsNonAdmin(): void
    {
        $this->client->request('POST', '/category/1/delete');

        $this->assertResponseRedirects('/login');
    }

    public function testDeleteCategoryWithIssues(): void
    {
        // Create a test category with an issue
        $category = new Category();
        $category->setName('Test Category');
        $category->setDescription('Test Description');
        $this->entityManager->persist($category);

        $adminUser = $this->getExistingAdminUser();
        $issue = new Issue();
        $issue->setTitle('Test Issue');
        $issue->setDescription('Test Description');
        $issue->setCategory($category);
        $issue->setAuthor($adminUser);
        $this->entityManager->persist($issue);
        $this->entityManager->flush();

        $this->client->loginUser($adminUser);

        // Get the CSRF token from the edit form
        $crawler = $this->client->request('GET', '/category/'.$category->getId().'/edit');
        $token = $crawler->filter('input[name="category[_token]"]')->attr('value');

        $this->client->request('POST', '/category/'.$category->getId().'/delete', [
            '_token' => $token,
        ]);

        $this->assertResponseRedirects('/category/');
        $this->client->followRedirect();
        $this->assertStringContainsString('Cannot delete category', $this->client->getResponse()->getContent());
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

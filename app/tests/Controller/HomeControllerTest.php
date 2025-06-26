<?php

namespace App\Tests\Controller;

use App\Entity\AdminUser;
use App\Entity\Category;
use App\Entity\Issue;
use App\Repository\AdminUserRepository;
use App\Repository\CategoryRepository;
use App\Repository\IssueRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testIndexPageIsAccessible(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h2', 'Issues');
    }

    public function testIndexPageShowsIssues(): void
    {
        $client = static::createClient();
        
        // Create test data
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        
        // Get admin user for author
        $adminUserRepository = static::getContainer()->get(AdminUserRepository::class);
        $adminUser = $adminUserRepository->findOneBy(['email' => 'admin@bugtracker.com']);
        
        $category = new Category();
        $category->setName('Test Category');
        $entityManager->persist($category);
        
        $issue = new Issue();
        $issue->setTitle('Test Issue');
        $issue->setDescription('Test Description');
        $issue->setCategory($category);
        $issue->setAuthor($adminUser);
        $entityManager->persist($issue);
        
        $entityManager->flush();

        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h5.card-title', 'Test Issue');
        $this->assertSelectorTextContains('.badge.bg-info', 'Test Category');
    }

    public function testIndexPageWithCategoryFilter(): void
    {
        $client = static::createClient();
        
        // Create test data
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        
        // Get admin user for author
        $adminUserRepository = static::getContainer()->get(AdminUserRepository::class);
        $adminUser = $adminUserRepository->findOneBy(['email' => 'admin@bugtracker.com']);
        
        $category = new Category();
        $category->setName('Test Category');
        $entityManager->persist($category);
        
        $issue = new Issue();
        $issue->setTitle('Test Issue');
        $issue->setDescription('Test Description');
        $issue->setCategory($category);
        $issue->setAuthor($adminUser);
        $entityManager->persist($issue);
        
        $entityManager->flush();

        $crawler = $client->request('GET', '/?category=' . $category->getId());

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h5.card-title', 'Test Issue');
    }

    public function testIndexPagePagination(): void
    {
        $client = static::createClient();
        
        // Create test data
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        
        // Get admin user for author
        $adminUserRepository = static::getContainer()->get(AdminUserRepository::class);
        $adminUser = $adminUserRepository->findOneBy(['email' => 'admin@bugtracker.com']);
        
        $category = new Category();
        $category->setName('Test Category');
        $entityManager->persist($category);
        
        // Create more than 10 issues to test pagination
        for ($i = 1; $i <= 15; $i++) {
            $issue = new Issue();
            $issue->setTitle("Test Issue $i");
            $issue->setDescription("Test Description $i");
            $issue->setCategory($category);
            $issue->setAuthor($adminUser);
            $entityManager->persist($issue);
        }
        
        $entityManager->flush();

        $crawler = $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.pagination');
    }
} 
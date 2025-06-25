<?php

namespace App\DataFixtures;

use App\Entity\AdminUser;
use App\Entity\Category;
use App\Entity\Issue;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $adminUser = new AdminUser();
        $adminUser->setEmail('admin@bugtracker.com');
        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'admin123'));
        $adminUser->setRoles(['ROLE_ADMIN']);
        $manager->persist($adminUser);

        // Create categories
        $categories = [
            'Bug' => 'Software bugs and defects that need to be fixed',
            'UI/UX' => 'User interface and user experience issues',
            'Performance' => 'Performance-related issues and optimizations',
            'Security' => 'Security vulnerabilities and authentication issues',
            'Database' => 'Database-related problems and data integrity issues',
            'API' => 'API endpoints and integration problems',
            'Mobile' => 'Mobile app specific issues and features',
            'Testing' => 'Testing framework and test coverage issues',
            'Documentation' => 'Documentation updates and improvements',
            'Deployment' => 'Deployment and infrastructure issues',
            'Accessibility' => 'Accessibility compliance and WCAG issues',
            'Internationalization' => 'Multi-language support and localization',
            'Backend' => 'Server-side logic and business rules',
            'Frontend' => 'Client-side JavaScript and CSS issues',
        ];

        $categoryEntities = [];
        foreach ($categories as $name => $description) {
            $category = new Category();
            $category->setName($name);
            $category->setDescription($description);
            $manager->persist($category);
            $categoryEntities[] = $category;
        }

        // Create sample issues
        $issues = [
            [
                'title' => 'Login page not responsive on mobile devices',
                'description' => 'The login form does not display properly on mobile devices. The input fields are too small and the submit button is cut off on smaller screens.',
                'status' => Issue::STATUS_OPEN,
                'priority' => Issue::PRIORITY_HIGH,
                'category' => 'UI/UX'
            ],
            [
                'title' => 'Database query optimization needed',
                'description' => 'The issue listing page is loading slowly due to inefficient database queries. Need to optimize the queries and add proper indexing.',
                'status' => Issue::STATUS_OPEN,
                'priority' => Issue::PRIORITY_HIGH,
                'category' => 'Performance'
            ],
            [
                'title' => 'Email notifications not working',
                'description' => 'Users are not receiving email notifications when issues are assigned to them. The email service configuration needs to be fixed.',
                'status' => Issue::STATUS_CLOSED,
                'priority' => Issue::PRIORITY_HIGH,
                'category' => 'Bug'
            ],
            [
                'title' => 'Fix pagination on mobile',
                'description' => 'The pagination controls are not touch-friendly on mobile devices and need to be redesigned.',
                'status' => Issue::STATUS_OPEN,
                'priority' => Issue::PRIORITY_MEDIUM,
                'category' => 'UI/UX'
            ],
            [
                'title' => 'Memory leak in issue listing',
                'description' => 'There is a memory leak when displaying large numbers of issues. Need to implement proper memory management.',
                'status' => Issue::STATUS_CLOSED,
                'priority' => Issue::PRIORITY_HIGH,
                'category' => 'Performance'
            ],
            [
                'title' => 'SQL injection vulnerability in search',
                'description' => 'The search functionality is vulnerable to SQL injection attacks. Need to implement proper parameterized queries.',
                'status' => Issue::STATUS_OPEN,
                'priority' => Issue::PRIORITY_HIGH,
                'category' => 'Security'
            ],
            [
                'title' => 'API rate limiting not working',
                'description' => 'The API endpoints are not properly rate limited, allowing potential abuse. Need to implement proper rate limiting.',
                'status' => Issue::STATUS_IN_PROGRESS,
                'priority' => Issue::PRIORITY_MEDIUM,
                'category' => 'API'
            ],
            [
                'title' => 'Database connection pooling needed',
                'description' => 'Database connections are not being pooled efficiently, causing connection exhaustion under load.',
                'status' => Issue::STATUS_OPEN,
                'priority' => Issue::PRIORITY_MEDIUM,
                'category' => 'Database'
            ],
            [
                'title' => 'Mobile app crashes on iOS 17',
                'description' => 'The mobile app crashes immediately on iOS 17 devices. Need to investigate compatibility issues.',
                'status' => Issue::STATUS_OPEN,
                'priority' => Issue::PRIORITY_HIGH,
                'category' => 'Mobile'
            ],
            [
                'title' => 'Unit test coverage below 80%',
                'description' => 'The current unit test coverage is only 65%. Need to add more tests to reach the 80% target.',
                'status' => Issue::STATUS_OPEN,
                'priority' => Issue::PRIORITY_LOW,
                'category' => 'Testing'
            ],
            [
                'title' => 'API documentation outdated',
                'description' => 'The API documentation is outdated and missing several new endpoints. Need to update the docs.',
                'status' => Issue::STATUS_IN_PROGRESS,
                'priority' => Issue::PRIORITY_LOW,
                'category' => 'Documentation'
            ],
            [
                'title' => 'Docker container memory limit exceeded',
                'description' => 'The Docker containers are exceeding their memory limits in production, causing crashes.',
                'status' => Issue::STATUS_OPEN,
                'priority' => Issue::PRIORITY_HIGH,
                'category' => 'Deployment'
            ],
            [
                'title' => 'Screen reader compatibility issues',
                'description' => 'The application is not fully compatible with screen readers. Need to improve accessibility.',
                'status' => Issue::STATUS_OPEN,
                'priority' => Issue::PRIORITY_MEDIUM,
                'category' => 'Accessibility'
            ],
            [
                'title' => 'Missing French translation strings',
                'description' => 'Several UI elements are not translated to French. Need to add missing translation keys.',
                'status' => Issue::STATUS_OPEN,
                'priority' => Issue::PRIORITY_LOW,
                'category' => 'Internationalization'
            ],
            [
                'title' => 'Backend validation logic inconsistent',
                'description' => 'The backend validation logic is inconsistent across different endpoints. Need to standardize.',
                'status' => Issue::STATUS_IN_PROGRESS,
                'priority' => Issue::PRIORITY_MEDIUM,
                'category' => 'Backend'
            ],
        ];

        foreach ($issues as $issueData) {
            $issue = new Issue();
            $issue->setTitle($issueData['title']);
            $issue->setDescription($issueData['description']);
            $issue->setStatus($issueData['status']);
            $issue->setPriority($issueData['priority']);
            
            // Find the category by name
            $category = null;
            foreach ($categoryEntities as $cat) {
                if ($cat->getName() === $issueData['category']) {
                    $category = $cat;
                    break;
                }
            }
            $issue->setCategory($category);
            
            $manager->persist($issue);
        }

        $manager->flush();
    }
} 
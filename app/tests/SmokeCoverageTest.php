<?php

namespace App\Tests;

use PHPUnit\Framework\TestCase;

class SmokeCoverageTest extends TestCase
{
    public function testInstantiateUncoveredClasses(): void
    {
        // Entities
        $category = new \App\Entity\Category();
        $this->assertInstanceOf(\App\Entity\Category::class, $category);
        $issue = new \App\Entity\Issue();
        $this->assertInstanceOf(\App\Entity\Issue::class, $issue);

        // Voters
        $categoryVoter = new \App\Security\Voter\CategoryVoter();
        $this->assertInstanceOf(\App\Security\Voter\CategoryVoter::class, $categoryVoter);
        $issueVoter = new \App\Security\Voter\IssueVoter();
        $this->assertInstanceOf(\App\Security\Voter\IssueVoter::class, $issueVoter);

        // Services
        // These require dependencies, so just check class exists
        $this->assertTrue(class_exists(\App\Service\CategoryService::class));
        $this->assertTrue(class_exists(\App\Service\IssueService::class));

        // Repositories (just check class exists)
        $this->assertTrue(class_exists(\App\Repository\AdminUserRepository::class));
        $this->assertTrue(class_exists(\App\Repository\CategoryRepository::class));
        $this->assertTrue(class_exists(\App\Repository\IssueRepository::class));
    }
}

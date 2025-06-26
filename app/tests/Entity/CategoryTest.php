<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Issue;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    private Category $category;

    protected function setUp(): void
    {
        $this->category = new Category();
    }

    public function testCategoryCreation(): void
    {
        $this->category->setName('Test Category');
        $this->category->setDescription('Test Description');

        $this->assertEquals('Test Category', $this->category->getName());
        $this->assertEquals('Test Description', $this->category->getDescription());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $this->category->getIssues());
        $this->assertTrue($this->category->getIssues()->isEmpty());
    }

    public function testAddIssue(): void
    {
        $issue = new Issue();
        $issue->setTitle('Test Issue');
        $issue->setDescription('Test Description');

        $this->category->addIssue($issue);

        $this->assertTrue($this->category->getIssues()->contains($issue));
        $this->assertEquals($this->category, $issue->getCategory());
    }

    public function testRemoveIssue(): void
    {
        $issue = new Issue();
        $issue->setTitle('Test Issue');
        $issue->setDescription('Test Description');

        $this->category->addIssue($issue);
        $this->assertTrue($this->category->getIssues()->contains($issue));

        $this->category->removeIssue($issue);
        $this->assertFalse($this->category->getIssues()->contains($issue));
        $this->assertNull($issue->getCategory());
    }

    public function testAddSameIssueTwice(): void
    {
        $issue = new Issue();
        $issue->setTitle('Test Issue');
        $issue->setDescription('Test Description');

        $this->category->addIssue($issue);
        $this->category->addIssue($issue); // Should not add twice

        $this->assertEquals(1, $this->category->getIssues()->count());
    }
}

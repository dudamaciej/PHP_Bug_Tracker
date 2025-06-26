<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use App\Entity\Issue;
use PHPUnit\Framework\TestCase;

class IssueTest extends TestCase
{
    private Issue $issue;
    private Category $category;

    protected function setUp(): void
    {
        $this->category = new Category();
        $this->category->setName('Test Category');

        $this->issue = new Issue();
        $this->issue->setTitle('Test Issue');
        $this->issue->setDescription('Test Description');
        $this->issue->setCategory($this->category);
    }

    public function testIssueCreation(): void
    {
        $this->assertEquals('Test Issue', $this->issue->getTitle());
        $this->assertEquals('Test Description', $this->issue->getDescription());
        $this->assertEquals($this->category, $this->issue->getCategory());
        $this->assertEquals(Issue::STATUS_OPEN, $this->issue->getStatus());
        $this->assertEquals(Issue::PRIORITY_MEDIUM, $this->issue->getPriority());
        $this->assertNotNull($this->issue->getCreatedAt());
    }

    public function testSetValidStatus(): void
    {
        $this->issue->setStatus(Issue::STATUS_IN_PROGRESS);
        $this->assertEquals(Issue::STATUS_IN_PROGRESS, $this->issue->getStatus());

        $this->issue->setStatus(Issue::STATUS_CLOSED);
        $this->assertEquals(Issue::STATUS_CLOSED, $this->issue->getStatus());
    }

    public function testSetInvalidStatus(): void
    {
        $issue = new Issue();

        // Since we removed validation from setters, this should not throw an exception
        // The validation is now handled by Symfony's form validation
        $issue->setStatus('invalid_status');

        $this->assertEquals('invalid_status', $issue->getStatus());
    }

    public function testSetValidPriority(): void
    {
        $this->issue->setPriority(Issue::PRIORITY_HIGH);
        $this->assertEquals(Issue::PRIORITY_HIGH, $this->issue->getPriority());

        $this->issue->setPriority(Issue::PRIORITY_LOW);
        $this->assertEquals(Issue::PRIORITY_LOW, $this->issue->getPriority());
    }

    public function testSetInvalidPriority(): void
    {
        $issue = new Issue();

        // Since we removed validation from setters, this should not throw an exception
        // The validation is now handled by Symfony's form validation
        $issue->setPriority('invalid_priority');

        $this->assertEquals('invalid_priority', $issue->getPriority());
    }

    public function testGetStatusChoices(): void
    {
        $choices = Issue::getStatusChoices();
        $this->assertArrayHasKey('Open', $choices);
        $this->assertArrayHasKey('In Progress', $choices);
        $this->assertArrayHasKey('Closed', $choices);
        $this->assertEquals(Issue::STATUS_OPEN, $choices['Open']);
        $this->assertEquals(Issue::STATUS_IN_PROGRESS, $choices['In Progress']);
        $this->assertEquals(Issue::STATUS_CLOSED, $choices['Closed']);
    }

    public function testGetPriorityChoices(): void
    {
        $choices = Issue::getPriorityChoices();
        $this->assertArrayHasKey('Low', $choices);
        $this->assertArrayHasKey('Medium', $choices);
        $this->assertArrayHasKey('High', $choices);
        $this->assertEquals(Issue::PRIORITY_LOW, $choices['Low']);
        $this->assertEquals(Issue::PRIORITY_MEDIUM, $choices['Medium']);
        $this->assertEquals(Issue::PRIORITY_HIGH, $choices['High']);
    }
}

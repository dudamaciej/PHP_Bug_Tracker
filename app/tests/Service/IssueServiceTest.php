<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Category;
use App\Entity\Issue;
use App\Repository\IssueRepository;
use App\Service\IssueService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Test case for IssueService.
 */
class IssueServiceTest extends TestCase
{
    private IssueService $issueService;
    private EntityManagerInterface $entityManager;
    private IssueRepository $issueRepository;
    private AuthorizationCheckerInterface $authorizationChecker;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->issueRepository = $this->createMock(IssueRepository::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->issueService = new IssueService(
            $this->entityManager,
            $this->issueRepository,
            $this->authorizationChecker
        );
    }

    public function testGetIssuesWithFilter(): void
    {
        $expectedIssues = [new Issue()];
        
        $this->issueRepository
            ->expects($this->once())
            ->method('findIssuesWithFilter')
            ->with(null, 1, 10, 'createdAt', 'DESC')
            ->willReturn($expectedIssues);

        $result = $this->issueService->getIssuesWithFilter();
        
        $this->assertEquals($expectedIssues, $result);
    }

    public function testCountIssuesWithFilter(): void
    {
        $expectedCount = 5;
        
        $this->issueRepository
            ->expects($this->once())
            ->method('countIssuesWithFilter')
            ->with(null)
            ->willReturn($expectedCount);

        $result = $this->issueService->countIssuesWithFilter();
        
        $this->assertEquals($expectedCount, $result);
    }

    public function testCreateIssueWithPermission(): void
    {
        $issue = new Issue();
        
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('ISSUE_CREATE')
            ->willReturn(true);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($issue);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->issueService->createIssue($issue);
    }

    public function testCreateIssueWithoutPermission(): void
    {
        $issue = new Issue();
        
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('ISSUE_CREATE')
            ->willReturn(false);

        $this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);
        $this->expectExceptionMessage('You do not have permission to create issues.');

        $this->issueService->createIssue($issue);
    }

    public function testUpdateIssueWithPermission(): void
    {
        $issue = new Issue();
        
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('ISSUE_EDIT', $issue)
            ->willReturn(true);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->issueService->updateIssue($issue);
    }

    public function testUpdateIssueWithoutPermission(): void
    {
        $issue = new Issue();
        
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('ISSUE_EDIT', $issue)
            ->willReturn(false);

        $this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);
        $this->expectExceptionMessage('You do not have permission to edit this issue.');

        $this->issueService->updateIssue($issue);
    }

    public function testDeleteIssueWithPermission(): void
    {
        $issue = new Issue();
        
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('ISSUE_DELETE', $issue)
            ->willReturn(true);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($issue);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->issueService->deleteIssue($issue);
    }

    public function testDeleteIssueWithoutPermission(): void
    {
        $issue = new Issue();
        
        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('ISSUE_DELETE', $issue)
            ->willReturn(false);

        $this->expectException(\Symfony\Component\Security\Core\Exception\AccessDeniedException::class);
        $this->expectExceptionMessage('You do not have permission to delete this issue.');

        $this->issueService->deleteIssue($issue);
    }

    public function testFindIssueWithPermission(): void
    {
        $issue = new Issue();
        
        $this->issueRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($issue);

        $this->authorizationChecker
            ->expects($this->never())
            ->method('isGranted');

        $result = $this->issueService->findIssue(1);
        
        $this->assertEquals($issue, $result);
    }

    public function testFindIssueWithoutPermission(): void
    {
        $issue = new Issue();
        
        $this->issueRepository
            ->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($issue);

        $this->authorizationChecker
            ->expects($this->never())
            ->method('isGranted');

        $result = $this->issueService->findIssue(1);
        
        $this->assertEquals($issue, $result);
    }

    public function testGetAllIssuesWithAdminPermission(): void
    {
        $expectedIssues = [new Issue()];
        
        // No authorization check needed anymore since getAllIssues is public
        $this->authorizationChecker
            ->expects($this->never())
            ->method('isGranted');

        $this->issueRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($expectedIssues);

        $result = $this->issueService->getAllIssues();
        
        $this->assertEquals($expectedIssues, $result);
    }

    public function testGetAllIssuesWithoutAdminPermission(): void
    {
        $expectedIssues = [new Issue()];
        
        // No authorization check needed anymore since getAllIssues is public
        $this->authorizationChecker
            ->expects($this->never())
            ->method('isGranted');

        $this->issueRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($expectedIssues);

        $result = $this->issueService->getAllIssues();
        
        $this->assertEquals($expectedIssues, $result);
    }
} 
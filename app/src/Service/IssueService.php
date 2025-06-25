<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Issue;
use App\Repository\IssueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Service for handling Issue business logic.
 */
class IssueService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private IssueRepository $issueRepository,
        private AuthorizationCheckerInterface $authorizationChecker
    ) {
    }

    /**
     * Get paginated issues with filtering.
     */
    public function getIssuesWithFilter(?int $categoryId = null, int $page = 1, int $limit = 10, string $sortBy = 'createdAt', string $sortOrder = 'DESC'): array
    {
        $offset = ($page - 1) * $limit;
        
        return $this->issueRepository->findIssuesWithFilter($categoryId, $page, $limit, $sortBy, $sortOrder);
    }

    /**
     * Count issues with filter.
     */
    public function countIssuesWithFilter(?int $categoryId = null): int
    {
        return $this->issueRepository->countIssuesWithFilter($categoryId);
    }

    /**
     * Create a new issue.
     */
    public function createIssue(Issue $issue): void
    {
        if (!$this->authorizationChecker->isGranted('ISSUE_CREATE')) {
            throw new AccessDeniedException('You do not have permission to create issues.');
        }

        $this->entityManager->persist($issue);
        $this->entityManager->flush();
    }

    /**
     * Update an existing issue.
     */
    public function updateIssue(Issue $issue): void
    {
        if (!$this->authorizationChecker->isGranted('ISSUE_EDIT', $issue)) {
            throw new AccessDeniedException('You do not have permission to edit this issue.');
        }

        $this->entityManager->flush();
    }

    /**
     * Delete an issue.
     */
    public function deleteIssue(Issue $issue): void
    {
        if (!$this->authorizationChecker->isGranted('ISSUE_DELETE', $issue)) {
            throw new AccessDeniedException('You do not have permission to delete this issue.');
        }

        $this->entityManager->remove($issue);
        $this->entityManager->flush();
    }

    /**
     * Find an issue by ID.
     */
    public function findIssue(int $id): ?Issue
    {
        $issue = $this->issueRepository->find($id);
        
        if ($issue && !$this->authorizationChecker->isGranted('ISSUE_VIEW', $issue)) {
            throw new AccessDeniedException('You do not have permission to view this issue.');
        }

        return $issue;
    }

    /**
     * Get all issues (for admin).
     */
    public function getAllIssues(): array
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('You do not have permission to view all issues.');
        }

        return $this->issueRepository->findAll();
    }
} 
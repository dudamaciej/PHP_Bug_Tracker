<?php

declare(strict_types=1);

/**
 * This file is part of the Bug Tracker application.
 *
 * (c) 2024 Bug Tracker Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service;

use App\Entity\Issue;
use App\Repository\IssueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Service for managing issues.
 */
class IssueService
{
    /**
     * Constructor.
     *
     * @param EntityManagerInterface        $entityManager
     * @param IssueRepository               $issueRepository
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(private EntityManagerInterface $entityManager, private IssueRepository $issueRepository, private AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    /**
     * Get issues with optional filtering and pagination.
     *
     * @param int|null $categoryId
     * @param int      $page
     * @param int      $limit
     * @param string   $sortBy
     * @param string   $sortOrder
     *
     * @return Issue[]
     */
    public function getIssuesWithFilter(?int $categoryId = null, int $page = 1, int $limit = 10, string $sortBy = 'createdAt', string $sortOrder = 'DESC'): array
    {
        $offset = ($page - 1) * $limit;

        return $this->issueRepository->findIssuesWithFilter($categoryId, $page, $limit, $sortBy, $sortOrder);
    }

    /**
     * Count issues with optional filtering.
     *
     * @param int|null $categoryId
     *
     * @return int
     */
    public function countIssuesWithFilter(?int $categoryId = null): int
    {
        return $this->issueRepository->countIssuesWithFilter($categoryId);
    }

    /**
     * Create a new issue.
     *
     * @param Issue $issue
     *
     * @return Issue
     */
    public function createIssue(Issue $issue): Issue
    {
        if (!$this->authorizationChecker->isGranted('ISSUE_CREATE')) {
            throw new AccessDeniedException('You do not have permission to create issues.');
        }

        $this->entityManager->persist($issue);
        $this->entityManager->flush();

        return $issue;
    }

    /**
     * Update an existing issue.
     *
     * @param Issue $issue
     *
     * @return Issue
     */
    public function updateIssue(Issue $issue): Issue
    {
        if (!$this->authorizationChecker->isGranted('ISSUE_EDIT', $issue)) {
            throw new AccessDeniedException('You do not have permission to edit this issue.');
        }

        $this->entityManager->flush();

        return $issue;
    }

    /**
     * Delete an issue.
     *
     * @param Issue $issue
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
     *
     * @param int $id
     *
     * @return Issue|null
     */
    public function findIssue(int $id): ?Issue
    {
        return $this->issueRepository->find($id);
    }

    /**
     * Get all issues.
     *
     * @return Issue[]
     */
    public function getAllIssues(): array
    {
        return $this->issueRepository->findAll();
    }
}

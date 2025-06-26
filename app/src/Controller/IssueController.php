<?php

/*
 * This file is part of the PHP Bug Tracker project.
 *
 * (c) 2024 PHP Bug Tracker Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Issue;
use App\Service\CategoryService;
use App\Service\IssueService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller for Issue management.
 */
#[Route('/issue')]
class IssueController extends AbstractController
{
    /**
     * Constructor.
     */
    public function __construct(private IssueService $issueService, private CategoryService $categoryService, private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Display the list of all issues.
     */
    #[Route('/', name: 'issue_index', methods: ['GET'])]
    public function index(): Response
    {
        $issues = $this->issueService->getAllIssues();

        return $this->render('issue/index.html.twig', [
            'issues' => $issues,
        ]);
    }

    /**
     * Test issue creation endpoint.
     */
    #[Route('/test-create', name: 'issue_test_create', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function testCreate(): Response
    {
        try {
            $categoryRepository = $this->entityManager->getRepository(\App\Entity\Category::class);
            $category = $categoryRepository->find(5); // Get first category

            if (!$category) {
                throw new \Exception('No category found');
            }

            $issue = new Issue();
            $issue->setTitle('Test Issue from Controller');
            $issue->setDescription('This is a test issue created directly from the controller.');
            $issue->setStatus(Issue::STATUS_OPEN);
            $issue->setPriority(Issue::PRIORITY_MEDIUM);
            $issue->setCategory($category);

            // Set the current user as the author
            $user = $this->getUser();
            if ($user instanceof \App\Entity\AdminUser) {
                $issue->setAuthor($user);
            } else {
                throw new \Exception('User not authenticated or not an admin user.');
            }

            $this->entityManager->persist($issue);
            $this->entityManager->flush();

            $this->addFlash('success', 'Test issue created successfully with ID: '.$issue->getId());
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error creating test issue: '.$e->getMessage());
        }

        return $this->redirectToRoute('issue_index');
    }

    /**
     * Create a new issue.
     */
    #[Route('/new', name: 'issue_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request): Response
    {
        // Get all categories for the form
        $categoryRepository = $this->entityManager->getRepository(\App\Entity\Category::class);
        $categories = $categoryRepository->findAll();

        if ($request->isMethod('POST')) {
            try {
                $formData = $request->request->all();
                $issueData = $formData['issue'] ?? [];

                // Validate required fields
                if (empty($issueData['title']) || empty($issueData['description']) || empty($issueData['category'])) {
                    throw new \Exception('Please fill in all required fields.');
                }

                $category = $categoryRepository->find($issueData['category']);

                if (!$category) {
                    throw new \Exception('Selected category not found.');
                }

                $issue = new Issue();
                $issue->setTitle($issueData['title']);
                $issue->setDescription($issueData['description']);
                $issue->setStatus($issueData['status'] ?? Issue::STATUS_OPEN);
                $issue->setPriority($issueData['priority'] ?? Issue::PRIORITY_MEDIUM);
                $issue->setCategory($category);

                // Set the current user as the author
                $user = $this->getUser();
                if ($user instanceof \App\Entity\AdminUser) {
                    $issue->setAuthor($user);
                } else {
                    throw new \Exception('User not authenticated or not an admin user.');
                }

                $this->entityManager->persist($issue);
                $this->entityManager->flush();

                $this->addFlash('success', 'Issue created successfully with ID: '.$issue->getId());

                return $this->redirectToRoute('issue_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error creating issue: '.$e->getMessage());
            }
        }

        return $this->render('issue/new.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * Display a specific issue.
     */
    #[Route('/{id}', name: 'issue_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        try {
            $issue = $this->issueService->findIssue($id);

            if (!$issue) {
                throw $this->createNotFoundException('Issue not found.');
            }

            return $this->render('issue/show.html.twig', [
                'issue' => $issue,
            ]);
        } catch (\AccessDeniedException $e) {
            throw $this->createAccessDeniedException($e->getMessage());
        }
    }

    /**
     * Edit an existing issue.
     */
    #[Route('/{id}/edit', name: 'issue_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, int $id): Response
    {
        try {
            $issue = $this->issueService->findIssue($id);

            if (!$issue) {
                throw $this->createNotFoundException('Issue not found.');
            }

            // Get all categories for the form
            $categoryRepository = $this->entityManager->getRepository(\App\Entity\Category::class);
            $categories = $categoryRepository->findAll();

            if ($request->isMethod('POST')) {
                $formData = $request->request->all();
                $issueData = $formData['issue'] ?? [];

                try {
                    $category = $categoryRepository->find($issueData['category'] ?? null);

                    if (!$category) {
                        throw new \Exception('Selected category not found.');
                    }

                    $issue->setTitle($issueData['title'] ?? '');
                    $issue->setDescription($issueData['description'] ?? '');
                    $issue->setStatus($issueData['status'] ?? Issue::STATUS_OPEN);
                    $issue->setPriority($issueData['priority'] ?? Issue::PRIORITY_MEDIUM);
                    $issue->setCategory($category);

                    $this->entityManager->flush();
                    $this->addFlash('success', 'Issue updated successfully.');

                    return $this->redirectToRoute('issue_show', ['id' => $issue->getId()]);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error updating issue: '.$e->getMessage());
                }
            }

            return $this->render('issue/edit.html.twig', [
                'issue' => $issue,
                'categories' => $categories,
            ]);
        } catch (\AccessDeniedException $e) {
            throw $this->createAccessDeniedException($e->getMessage());
        }
    }

    /**
     * Delete an issue.
     */
    #[Route('/{id}/delete', name: 'issue_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, int $id): Response
    {
        try {
            $issue = $this->issueService->findIssue($id);

            if (!$issue) {
                throw $this->createNotFoundException('Issue not found.');
            }

            if ($this->isCsrfTokenValid('delete'.$issue->getId(), $request->request->get('_token'))) {
                $this->issueService->deleteIssue($issue);
                $this->addFlash('success', 'Issue deleted successfully.');
            } else {
                $this->addFlash('error', 'Invalid CSRF token.');
            }
        } catch (\AccessDeniedException $e) {
            throw $this->createAccessDeniedException($e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error deleting issue: '.$e->getMessage());
        }

        return $this->redirectToRoute('issue_index');
    }
}

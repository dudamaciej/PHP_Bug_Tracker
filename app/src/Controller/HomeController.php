<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CategoryService;
use App\Service\IssueService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller for public homepage and issue listing.
 */
class HomeController extends AbstractController
{
    public function __construct(
        private IssueService $issueService,
        private CategoryService $categoryService
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $categoryId = $request->query->getInt('category', 0);
        $categoryId = $categoryId > 0 ? $categoryId : null;
        
        // Get sorting parameters
        $sortBy = $request->query->get('sort', 'createdAt');
        $sortOrder = $request->query->get('order', 'DESC');

        $issues = $this->issueService->getIssuesWithFilter($categoryId, $page, 10, $sortBy, $sortOrder);
        $totalIssues = $this->issueService->countIssuesWithFilter($categoryId);
        $categories = $this->categoryService->getAllCategoriesOrdered();

        $totalPages = ceil($totalIssues / 10);

        return $this->render('home/index.html.twig', [
            'issues' => $issues,
            'categories' => $categories,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'selected_category' => $categoryId,
            'total_issues' => $totalIssues,
            'sort_by' => $sortBy,
            'sort_order' => $sortOrder,
        ]);
    }
} 
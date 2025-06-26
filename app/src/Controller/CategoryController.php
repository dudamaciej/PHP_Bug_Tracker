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

use App\Entity\Category;
use App\Form\CategoryType;
use App\Service\CategoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller for Category management.
 */
#[Route('/category')]
class CategoryController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param CategoryService        $categoryService
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(private CategoryService $categoryService, private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Display the list of all categories.
     *
     * @return Response
     */
    #[Route('/', name: 'category_index', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $categories = $this->categoryService->getAllCategories();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    /**
     * Create a new category.
     *
     * @param Request $request
     *
     * @return Response
     */
    #[Route('/new', name: 'category_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            // Get all POST data and extract category fields
            $postData = $request->request->all();
            $formData = $postData['category'] ?? [];
            $name = $formData['name'] ?? '';
            $description = $formData['description'] ?? '';

            if ($name) {
                try {
                    $category = new Category();
                    $category->setName($name);
                    $category->setDescription($description);

                    $this->categoryService->createCategory($category);
                    $this->addFlash('success', 'Category created successfully.');

                    return $this->redirectToRoute('category_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Error creating category: '.$e->getMessage());

                    return $this->redirectToRoute('category_new');
                }
            }

            $this->addFlash('error', 'Category name is required.');

            return $this->redirectToRoute('category_new');
        }

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        return $this->render('category/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Display a specific category.
     *
     * @param int $id
     *
     * @return Response
     */
    #[Route('/{id}', name: 'category_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        try {
            $category = $this->categoryService->findCategory($id);

            if (!$category) {
                throw $this->createNotFoundException('Category not found.');
            }

            return $this->render('category/show.html.twig', [
                'category' => $category,
            ]);
        } catch (\AccessDeniedException $e) {
            throw $this->createAccessDeniedException($e->getMessage());
        }
    }

    /**
     * Edit an existing category.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    #[Route('/{id}/edit', name: 'category_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Request $request, int $id): Response
    {
        try {
            $category = $this->categoryService->findCategory($id);

            if (!$category) {
                throw $this->createNotFoundException('Category not found.');
            }

            $form = $this->createForm(CategoryType::class, $category);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $this->categoryService->updateCategory($category);
                $this->addFlash('success', 'Category updated successfully.');

                return $this->redirectToRoute('category_show', ['id' => $category->getId()]);
            }

            return $this->render('category/edit.html.twig', [
                'category' => $category,
                'form' => $form->createView(),
            ]);
        } catch (\AccessDeniedException $e) {
            throw $this->createAccessDeniedException($e->getMessage());
        }
    }

    /**
     * Delete a category.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    #[Route('/{id}/delete', name: 'category_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Request $request, int $id): Response
    {
        try {
            $category = $this->categoryService->findCategory($id);

            if (!$category) {
                throw $this->createNotFoundException('Category not found.');
            }

            if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
                $this->categoryService->deleteCategory($category);
                $this->addFlash('success', 'Category deleted successfully.');
            } else {
                $this->addFlash('error', 'Invalid CSRF token.');
            }
        } catch (\AccessDeniedException $e) {
            $this->addFlash('error', 'Access denied: '.$e->getMessage());
        } catch (\InvalidArgumentException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error deleting category: '.$e->getMessage());
        }

        return $this->redirectToRoute('category_index');
    }
}

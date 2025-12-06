<?php

declare(strict_types=1);

namespace Examples\Api\Http\Controllers;

use Examples\Api\Models\Category;
use Framework\Http\Request;
use Framework\Http\Response;

class CategoryController
{
    /**
     * Get all categories
     */
    public function index(): Response
    {
        $categories = Category::query()->orderBy('name', 'asc')->get();

        $include = $_GET['include'] ?? null;
        $data = $categories->map(function ($category) use ($include) {
            $categoryData = $category->toArray();
            
            if ($include === 'tasks') {
                $categoryData['tasks'] = $category->tasks()->toArray();
            }
            
            return $categoryData;
        })->all();

        return $this->jsonResponse([
            'data' => $data,
            'count' => count($data),
        ]);
    }

    /**
     * Get a single category by ID
     */
    public function show(int $id, Request $request): Response
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->jsonResponse([
                'error' => 'Category not found',
            ], 404);
        }

        $data = $category->toArray();

        $include = $request->query('include');
        if ($include === 'tasks') {
            $data['tasks'] = $category->tasks()->toArray();
        }

        return $this->jsonResponse([
            'data' => $data,
        ]);
    }

    /**
     * Create a new category
     */
    public function store(Request $request): Response
    {
        $data = $request->isJson() ? ($request->json() ?? []) : $request->input();

        if (empty($data['name'])) {
            return $this->jsonResponse([
                'error' => 'Validation failed',
                'errors' => ['name' => 'Name is required'],
            ], 422);
        }

        $category = new Category([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        if ($category->save()) {
            return $this->jsonResponse([
                'message' => 'Category created successfully',
                'data' => $category->toArray(),
            ], 201);
        }

        return $this->jsonResponse([
            'error' => 'Failed to create category',
        ], 500);
    }

    /**
     * Update an existing category
     */
    public function update(int $id, Request $request): Response
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->jsonResponse([
                'error' => 'Category not found',
            ], 404);
        }

        $data = $request->isJson() ? ($request->json() ?? []) : $request->input();

        if (isset($data['name'])) {
            $category->setAttribute('name', $data['name']);
        }
        if (isset($data['description'])) {
            $category->setAttribute('description', $data['description']);
        }

        if ($category->save()) {
            return $this->jsonResponse([
                'message' => 'Category updated successfully',
                'data' => $category->toArray(),
            ]);
        }

        return $this->jsonResponse([
            'error' => 'Failed to update category',
        ], 500);
    }

    /**
     * Delete a category
     */
    public function destroy(int $id): Response
    {
        $category = Category::find($id);

        if (!$category) {
            return $this->jsonResponse([
                'error' => 'Category not found',
            ], 404);
        }

        if ($category->delete()) {
            return $this->jsonResponse([
                'message' => 'Category deleted successfully',
            ]);
        }

        return $this->jsonResponse([
            'error' => 'Failed to delete category',
        ], 500);
    }

    /**
     * Helper to parse JSON request body
     */
    protected function parseJsonBody(Request $request): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        
        if (str_contains($contentType, 'application/json')) {
            $rawBody = file_get_contents('php://input');
            $data = json_decode($rawBody, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data ?? [];
            }
        }
        
        return $_POST ?? [];
    }

    /**
     * Create a JSON response
     */
    protected function jsonResponse(array $data, int $status = 200): Response
    {
        return new Response(
            json_encode($data, JSON_PRETTY_PRINT),
            $status,
            ['Content-Type' => 'application/json']
        );
    }
}


<?php

declare(strict_types=1);

namespace Examples\Api\Http\Controllers;

use Examples\Api\Models\Task;
use Framework\Http\Request;
use Framework\Http\Response;

class TaskController
{
    /**
     * Get all tasks with optional filtering
     */
    public function index(Request $request): Response
    {
        $query = Task::query();

        // Filter by status
        if ($status = $request->query('status')) {
            $query->where('status', '=', $status);
        }

        // Filter by priority
        if ($priority = $request->query('priority')) {
            $query->where('priority', '=', $priority);
        }

        // Filter by category_id
        if ($categoryId = $request->query('category_id')) {
            $query->where('category_id', '=', $categoryId);
        }

        // Get tasks from query
        $tasks = $query->orderBy('created_at', 'desc')->get();

        // Search by title/description if requested
        if ($search = $request->query('search')) {
            $tasks = $tasks->filter(function ($task) use ($search) {
                $title = $task->getAttribute('title') ?? '';
                $description = $task->getAttribute('description') ?? '';
                return stripos($title, $search) !== false ||
                       stripos($description, $search) !== false;
            });
        }

        // Include relationships if requested
        $include = $request->query('include');
        $data = $tasks->map(function ($task) use ($include) {
            $taskData = $task->toArray();
            
            if ($include === 'category' && $task->category()) {
                $taskData['category'] = $task->category()->toArray();
            }
            
            return $taskData;
        })->all();

        return $this->jsonResponse([
            'data' => $data,
            'count' => count($data),
        ]);
    }

    /**
     * Get a single task by ID
     */
    public function show(int $id, Request $request): Response
    {
        $task = Task::find($id);

        if (!$task) {
            return $this->jsonResponse([
                'error' => 'Task not found',
            ], 404);
        }

        $data = $task->toArray();

        // Include category if requested
        $include = $request->query('include');
        if ($include === 'category' && $task->category()) {
            $data['category'] = $task->category()->toArray();
        }

        return $this->jsonResponse([
            'data' => $data,
        ]);
    }

    /**
     * Create a new task
     */
    public function store(Request $request): Response
    {
        $data = $request->isJson() ? ($request->json() ?? []) : $request->input();

        // Validation
        $errors = $this->validateTaskData($data);
        if (!empty($errors)) {
            return $this->jsonResponse([
                'error' => 'Validation failed',
                'errors' => $errors,
            ], 422);
        }

        $task = new Task([
            'title' => $data['title'] ?? '',
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'pending',
            'priority' => $data['priority'] ?? 'medium',
            'category_id' => $data['category_id'] ?? null,
            'due_date' => $data['due_date'] ?? null,
        ]);

        if ($task->save()) {
            return $this->jsonResponse([
                'message' => 'Task created successfully',
                'data' => $task->toArray(),
            ], 201);
        }

        return $this->jsonResponse([
            'error' => 'Failed to create task',
        ], 500);
    }

    /**
     * Update an existing task
     */
    public function update(int $id, Request $request): Response
    {
        $task = Task::find($id);

        if (!$task) {
            return $this->jsonResponse([
                'error' => 'Task not found',
            ], 404);
        }

        $data = $request->isJson() ? ($request->json() ?? []) : $request->input();

        // Validation (only validate provided fields)
        $errors = $this->validateTaskData($data, false);
        if (!empty($errors)) {
            return $this->jsonResponse([
                'error' => 'Validation failed',
                'errors' => $errors,
            ], 422);
        }

        // Update only provided fields
        $updateableFields = ['title', 'description', 'status', 'priority', 'category_id', 'due_date'];
        foreach ($updateableFields as $field) {
            if (array_key_exists($field, $data)) {
                $task->setAttribute($field, $data[$field]);
            }
        }

        if ($task->save()) {
            return $this->jsonResponse([
                'message' => 'Task updated successfully',
                'data' => $task->toArray(),
            ]);
        }

        return $this->jsonResponse([
            'error' => 'Failed to update task',
        ], 500);
    }

    /**
     * Delete a task
     */
    public function destroy(int $id): Response
    {
        $task = Task::find($id);

        if (!$task) {
            return $this->jsonResponse([
                'error' => 'Task not found',
            ], 404);
        }

        if ($task->delete()) {
            return $this->jsonResponse([
                'message' => 'Task deleted successfully',
            ]);
        }

        return $this->jsonResponse([
            'error' => 'Failed to delete task',
        ], 500);
    }

    /**
     * Helper to parse JSON request body (deprecated - use $request->input() or $request->json() instead)
     * @deprecated Use $request->input() or $request->json() instead
     */
    protected function parseJsonBody(Request $request): array
    {
        // Use the new Request methods
        return $request->isJson() ? ($request->json() ?? []) : $request->input();
    }

    /**
     * Validate task data
     */
    protected function validateTaskData(array $data, bool $requireTitle = true): array
    {
        $errors = [];

        if ($requireTitle && empty($data['title'])) {
            $errors['title'] = 'Title is required';
        }

        if (isset($data['title']) && strlen($data['title']) > 255) {
            $errors['title'] = 'Title must be less than 255 characters';
        }

        if (isset($data['status'])) {
            $validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];
            if (!in_array($data['status'], $validStatuses, true)) {
                $errors['status'] = 'Status must be one of: ' . implode(', ', $validStatuses);
            }
        }

        if (isset($data['priority'])) {
            $validPriorities = ['low', 'medium', 'high', 'urgent'];
            if (!in_array($data['priority'], $validPriorities, true)) {
                $errors['priority'] = 'Priority must be one of: ' . implode(', ', $validPriorities);
            }
        }

        return $errors;
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

    /**
     * Helper to parse JSON request body (deprecated - use $request->input() or $request->json() instead)
     * @deprecated Use $request->input() or $request->json() instead
     */
    protected function parseJsonBody(Request $request): array
    {
        // Use the new Request methods
        return $request->isJson() ? ($request->json() ?? []) : $request->input();
    }
}


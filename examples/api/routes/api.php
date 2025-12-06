<?php

declare(strict_types=1);

use Framework\Routing\Router;
use Framework\Http\Response;
use Examples\Api\Http\Controllers\TaskController;
use Examples\Api\Http\Controllers\CategoryController;

return function (Router $router): void {
    // API Info endpoint
    $router->get('/', function () {
        return new Response(
            json_encode([
                'message' => 'Task Management API',
                'version' => '1.0.0',
                'endpoints' => [
                    'GET    /tasks' => 'List all tasks',
                    'GET    /tasks/{id}' => 'Get a single task',
                    'POST   /tasks' => 'Create a new task',
                    'PUT    /tasks/{id}' => 'Update a task',
                    'DELETE /tasks/{id}' => 'Delete a task',
                    'GET    /categories' => 'List all categories',
                    'GET    /categories/{id}' => 'Get a single category',
                    'POST   /categories' => 'Create a new category',
                    'PUT    /categories/{id}' => 'Update a category',
                    'DELETE /categories/{id}' => 'Delete a category',
                ],
            ], JSON_PRETTY_PRINT),
            200,
            ['Content-Type' => 'application/json']
        );
    });

    // Task routes (RESTful)
    $router->get('/tasks', [TaskController::class, 'index']);
    $router->get('/tasks/{id}', [TaskController::class, 'show']);
    $router->post('/tasks', [TaskController::class, 'store']);
    $router->put('/tasks/{id}', [TaskController::class, 'update']);
    $router->delete('/tasks/{id}', [TaskController::class, 'destroy']);

    // Category routes (RESTful)
    $router->get('/categories', [CategoryController::class, 'index']);
    $router->get('/categories/{id}', [CategoryController::class, 'show']);
    $router->post('/categories', [CategoryController::class, 'store']);
    $router->put('/categories/{id}', [CategoryController::class, 'update']);
    $router->delete('/categories/{id}', [CategoryController::class, 'destroy']);

};


<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\KanbanController;
use Framework\Routing\Router;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\View\View;
use App\Models\User;

return function (Router $router): void {
    // ... existing routes ...

    // SPA route for the Kanban app (you can reuse /app/* if you want)
    $router->spa('/app/*', function () {
        // boardId 1 is the demo board (auto-created if missing)
        return spa('KanbanApp', ['boardId' => 1]);
    });

    // API routes for Kanban
    $router->get('/api/kanban/boards', [KanbanController::class, 'index']);
    $router->get('/api/kanban/boards/{id}', [KanbanController::class, 'show']);
    $router->post('/api/kanban/boards/{id}/tasks', [KanbanController::class, 'storeTask']);
    $router->put('/api/kanban/tasks/{id}', [KanbanController::class, 'updateTask']);
    $router->post('/api/kanban/tasks/{id}/move', [KanbanController::class, 'moveTask']);
    $router->post('/api/kanban/tasks/reorder', [KanbanController::class, 'updateTaskPositions']);
    $router->delete('/api/kanban/tasks/{id}', [KanbanController::class, 'destroyTask']);
};

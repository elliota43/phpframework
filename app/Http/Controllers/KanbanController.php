<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\Column;
use App\Models\Task;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Support\Collection;

class KanbanController extends Controller
{
    public function index(Request $request): Response
    {
        $boards = Board::all();

        if ($boards->isEmpty()) {
            $this->seedDefaultBoard();
            $boards = Board::all();
        }

        $data = $boards->map(fn (Board $board) => $board->toArray())->all();

        return $this->jsonResponse([
            'data' => $data,
            'count' => count($data),
        ]);
    }

    public function show(int $id, Request $request): Response
    {
        $board = Board::find($id);
        if (!$board) {
            // if there are zero boards at all, create demo board
            $hasAnyBoards = !Board::all()->isEmpty();

            if (!$hasAnyBoards) {
                // fresh db, seed and use new board
                $board = $this->seedDefaultBoard();
            } else {
                return $this->jsonResponse(['error' => 'Board not found']);
            }
        }

        $columns = $board->columns();

        $data = $board->toArray();
        $data['columns'] = array_map(function (Column $column) {
            $colData = $column->toArray();
            $colData['tasks'] = array_map(
                fn (Task $task) => $task->toArray(),
                $column->tasks()->all()
            );
            return $colData;
        }, $columns->all());
        
        return $this->jsonResponse([
            'data' => $data,
        ]);
    }

    public function storeTask(int $id, Request $request): Response
    {
        $board = Board::find($id);
        if (!$board) {
            return $this->jsonResponse(['error' => 'Board not found'], 404);
        }
        $payload = $request->json() ?? $request->input();

        $title = trim((string)($payload['title'] ?? ''));
        if ($title === '') {
            return $this->jsonResponse(['error' => 'Title is required'], 422);
        }

        $columnId = isset($payload['column_id']) ? (int)$payload['column_id'] : null;

        /** @var Column|null $column */
        $column = $columnId ? Column::find($columnId) : null;
        if (!$column) {
            // Fallback to first column on the board
            $column = $board->columns()->first();
        }

        if (!$column) {
            return $this->jsonResponse(['error' => 'No columns available on this board'], 422);
        }

        $nextPosition = $this->nextPositionForColumn((int)$column->getAttribute('id'));

        $task = new Task([
            'board_id'    => (int)$board->getAttribute('id'),
            'column_id'   => (int)$column->getAttribute('id'),
            'title'       => $title,
            'description' => $payload['description'] ?? null,
            'status'      => $payload['status'] ?? 'open',
            'priority'    => $payload['priority'] ?? 'medium',
            'assignee'    => $payload['assignee'] ?? null,
            'due_date'    => $payload['due_date'] ?? null,
            'position'    => $nextPosition,
        ]);

        try {
            if ($task->save()) {
                return $this->jsonResponse([
                    'message' => 'Task created',
                    'data'    => $task->toArray(),
                ], 201);
            }

            return $this->jsonResponse(['error' => 'Failed to create task'], 500);
        } catch (\Throwable $e) {
            return $this->jsonResponse([
                'error' => 'Failed to create task',
                'message' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine(),
            ], 500);
        }
    }
    /**
     * Update a task.
     */
    public function updateTask(int $id, Request $request): Response
    {
        $task = Task::find($id);
        if (!$task) {
            return $this->jsonResponse(['error' => 'Task not found'], 404);
        }

        $payload = $request->json() ?? $request->input();

        $updates = [];

        foreach (['title', 'description', 'status', 'priority', 'assignee', 'due_date'] as $field) {
            if (array_key_exists($field, $payload)) {
                $updates[$field] = $payload[$field];
            }
        }

        if ($updates === []) {
            return $this->jsonResponse(['message' => 'No changes'], 200);
        }

        if ($task->update($updates)) {
            return $this->jsonResponse([
                'message' => 'Task updated',
                'data'    => $task->fresh()?->toArray(),
            ]);
        }

        return $this->jsonResponse(['error' => 'Failed to update task'], 500);
    }

    /**
     * Move a task between columns (or within the same column).
     */
    public function moveTask(int $id, Request $request): Response
    {
        $task = Task::find($id);
        if (!$task) {
            return $this->jsonResponse(['error' => 'Task not found'], 404);
        }

        $payload = $request->json() ?? $request->input();

        $targetColumnId = isset($payload['column_id'])
            ? (int)$payload['column_id']
            : null;

        if (!$targetColumnId) {
            return $this->jsonResponse(['error' => 'column_id is required'], 422);
        }

        $column = Column::find($targetColumnId);
        if (!$column) {
            return $this->jsonResponse(['error' => 'Target column not found'], 404);
        }

        // Support explicit position for reordering
        $targetPosition = isset($payload['position']) ? (int)$payload['position'] : null;
        
        // If no explicit position, append to end
        if ($targetPosition === null) {
            $targetPosition = $this->nextPositionForColumn($targetColumnId);
        } else {
            // Shift other tasks if needed
            $this->shiftTaskPositions($targetColumnId, $targetPosition, $id);
        }

        $task->setAttribute('column_id', $targetColumnId);
        $task->setAttribute('position', $targetPosition);

        if ($task->save()) {
            return $this->jsonResponse([
                'message' => 'Task moved',
                'data'    => $task->fresh()?->toArray(),
            ]);
        }

        return $this->jsonResponse(['error' => 'Failed to move task'], 500);
    }
    
    /**
     * Update task positions in bulk (for drag-and-drop reordering).
     */
    public function updateTaskPositions(Request $request): Response
    {
        $payload = $request->json() ?? $request->input();
        $updates = $payload['updates'] ?? [];
        
        if (empty($updates)) {
            return $this->jsonResponse(['error' => 'No updates provided'], 422);
        }
        
        foreach ($updates as $update) {
            $taskId = isset($update['task_id']) ? (int)$update['task_id'] : null;
            $columnId = isset($update['column_id']) ? (int)$update['column_id'] : null;
            $position = isset($update['position']) ? (int)$update['position'] : null;
            
            if ($taskId === null || $columnId === null || $position === null) {
                continue;
            }
            
            $task = Task::find($taskId);
            if (!$task) {
                continue;
            }
            
            $task->setAttribute('column_id', $columnId);
            $task->setAttribute('position', $position);
            $task->save();
        }
        
        return $this->jsonResponse([
            'message' => 'Task positions updated',
            'count' => count($updates),
        ]);
    }

    /**
     * Delete a task.
     */
    public function destroyTask(int $id): Response
    {
        $task = Task::find($id);
        if (!$task) {
            return $this->jsonResponse(['error' => 'Task not found'], 404);
        }

        if ($task->delete()) {
            return $this->jsonResponse(['message' => 'Task deleted']);
        }

        return $this->jsonResponse(['error' => 'Failed to delete task'], 500);
    }

    // ----------------- helpers -----------------

    protected function jsonResponse(array $data, int $status = 200): Response
    {
        return new Response(
            json_encode($data, JSON_PRETTY_PRINT),
            $status,
            ['Content-Type' => 'application/json']
        );
    }

    protected function nextPositionForColumn(int $columnId): int
    {
        $query = Task::query()
            ->where('column_id', '=', $columnId)
            ->orderBy('position', 'desc');

        $last = $query->first();

        if (!$last) {
            return 0;
        }

        return ((int)($last->getAttribute('position') ?? 0)) + 1;
    }
    
    /**
     * Shift task positions when inserting a task at a specific position.
     */
    protected function shiftTaskPositions(int $columnId, int $targetPosition, int $excludeTaskId): void
    {
        $tasks = Task::query()
            ->where('column_id', '=', $columnId)
            ->where('id', '!=', $excludeTaskId)
            ->where('position', '>=', $targetPosition)
            ->get();
            
        foreach ($tasks as $task) {
            $currentPosition = (int)($task->getAttribute('position') ?? 0);
            $task->setAttribute('position', $currentPosition + 1);
            $task->save();
        }
    }

    /**
     * Seed an initial board with three columns if nothing exists.
     */
    protected function seedDefaultBoard(): Board
    {
        $board = new Board(['name' => 'Demo Board']);
        $board->save();

        $boardId = (int)$board->getAttribute('id');

        $columns = [
            ['name' => 'Backlog', 'position' => 0],
            ['name' => 'In Progress', 'position' => 1],
            ['name' => 'Done', 'position' => 2],
        ];

        foreach ($columns as $col) {
            $column = new Column([
                'board_id'  => $boardId,
                'name'      => $col['name'],
                'position'  => $col['position'],
                'wip_limit' => null,
            ]);
            $column->save();
        }

        return $board;
    }
}